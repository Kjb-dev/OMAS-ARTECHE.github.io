<?php
// 1. Declare namespace imports at the top
use Shuchkin\SimpleXLSX;

// 2. ALWAYS start session before checking $_SESSION variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Include dependencies
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/SimpleXLSX.php';

// 4. Set response headers
header('Content-Type: application/json; charset=utf-8');

// 1. Session Guard
if (!isset($_SESSION['staff_logged_in']) || $_SESSION['staff_logged_in'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please log in.']);
    exit();
}

// 2. Fetch Dashboard Analytics Data (GET)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_dashboard_data') {
    $year = isset($_GET['year']) ? (int)$_GET['year'] : 2026;
    $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    $series = [
        'rice' => array_fill(0, 12, 0),
        'corn_cassava' => array_fill(0, 12, 0),
        'hvc' => array_fill(0, 12, 0),
        'fishery' => array_fill(0, 12, 0),
        'livestock' => array_fill(0, 12, 0)
    ];

    $totals = ['rice' => 0, 'corn_cassava' => 0, 'hvc' => 0, 'fishery' => 0, 'livestock' => 0];

    // Total registered farmer counts per commodity
    $registered_farmers = [
        'rice' => 0,
        'corn_cassava' => 0,
        'hvc' => 0,
        'fishery' => 0,
        'livestock' => 0
    ];

    // Total farm area breakdown in hectares
    $total_areas = [
        'rice'          => 0.00,
        'vegetable'     => 0.00,
        'rootcrops'     => 0.00,
        'fruit_trees'   => 0.00,
        'coconut_trees' => 0.00,
        'banana'        => 0.00,
        'corn'          => 0.00,
        'cassava'       => 0.00
    ];

    try {
        // Fetch Monthly Yield Commodity Reports Data
        $stmt = $pdo->prepare("SELECT commodity_type, report_month, yield_metric_tons FROM commodity_reports WHERE report_year = ?");
        $stmt->execute([$year]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $type = $row['commodity_type'];
            $month_idx = (int)$row['report_month'] - 1;
            $yield = (float)$row['yield_metric_tons'];

            if (isset($series[$type]) && $month_idx >= 0 && $month_idx < 12) {
                $series[$type][$month_idx] = $yield;
                $totals[$type] += $yield;
            }
        }

        // Masterlist database table names for farmer counts
        $tables = [
            'rice'         => 'rice_farmers',
            'corn_cassava' => 'corn_and_cassava',
            'hvc'          => 'hvc_farmer',
            'fishery'      => 'fishery_farmer',
            'livestock'    => 'farmers' 
        ];

        foreach ($tables as $key => $table_name) {
            try {
                $count_stmt = $pdo->query("SELECT COUNT(*) FROM {$table_name}");
                $registered_farmers[$key] = (int)$count_stmt->fetchColumn();
            } catch (PDOException $e) {
                $registered_farmers[$key] = 0;
            }
        }

        // 1. Rice Area Sum (pulls from rice_parcels if available, else rice_farmers)
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(rice_area AS DECIMAL(10,2))), 0) FROM rice_parcels");
            $total_areas['rice'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) {
            try {
                $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(rice_area AS DECIMAL(10,2))), 0) FROM rice_farmers");
                $total_areas['rice'] = (float)$stmt->fetchColumn();
            } catch (PDOException $ex) { $total_areas['rice'] = 0.00; }
        }

        // 2. High Value Crops - Vegetables Sum
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(area_has AS DECIMAL(10,2))), 0) FROM hvc_farmer WHERE LOWER(hvc_crop_type) LIKE '%veg%'");
            $total_areas['vegetable'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) { $total_areas['vegetable'] = 0.00; }

        // 3. High Value Crops - Rootcrops Sum
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(area_has AS DECIMAL(10,2))), 0) FROM hvc_farmer WHERE LOWER(hvc_crop_type) LIKE '%root%'");
            $total_areas['rootcrops'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) { $total_areas['rootcrops'] = 0.00; }

        // 4. High Value Crops - Fruit Trees Sum
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(area_has AS DECIMAL(10,2))), 0) FROM hvc_farmer WHERE LOWER(hvc_crop_type) LIKE '%fruit%'");
            $total_areas['fruit_trees'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) { $total_areas['fruit_trees'] = 0.00; }

        // 5. High Value Crops - Coconut Trees Sum
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(area_has AS DECIMAL(10,2))), 0) FROM hvc_farmer WHERE LOWER(hvc_crop_type) LIKE '%coco%' OR LOWER(hvc_crop_type) LIKE '%coconut%'");
            $total_areas['coconut_trees'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) { $total_areas['coconut_trees'] = 0.00; }

        // 6. High Value Crops - Banana Sum
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(area_has AS DECIMAL(10,2))), 0) FROM hvc_farmer WHERE LOWER(hvc_crop_type) LIKE '%banana%'");
            $total_areas['banana'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) { $total_areas['banana'] = 0.00; }

        // 7. Corn Area Sum
        try {
            $stmt = $pdo->query("SELECT COALESCE(SUM(CAST(corn_area_has AS DECIMAL(10,2))), 0) FROM corn_and_cassava");
            $total_areas['corn'] = (float)$stmt->fetchColumn();
        } catch (PDOException $e) { $total_areas['corn'] = 0.00; }

        // 8. Cassava Area Sum
        $cassava_cc_area = 0.00;
        $cassava_hvc_area = 0.00;

        try {
            $stmt_cassava_cc = $pdo->query("SELECT COALESCE(SUM(CAST(cassava_area_has AS DECIMAL(10,2))), 0) FROM corn_and_cassava");
            $cassava_cc_area = (float)$stmt_cassava_cc->fetchColumn();
        } catch (PDOException $e) { $cassava_cc_area = 0.00; }

        try {
            $stmt_cassava_hvc = $pdo->query("SELECT COALESCE(SUM(CAST(area_has AS DECIMAL(10,2))), 0) FROM hvc_farmer WHERE LOWER(hvc_crop_type) LIKE '%cassava%'");
            $cassava_hvc_area = (float)$stmt_cassava_hvc->fetchColumn();
        } catch (PDOException $e) { $cassava_hvc_area = 0.00; }

        $total_areas['cassava'] = $cassava_cc_area + $cassava_hvc_area;

        echo json_encode([
            'success' => true,
            'months' => $months,
            'series' => $series,
            'totals' => $totals,
            'registered_farmers' => $registered_farmers,
            'total_areas' => $total_areas
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
    }
    exit();
}

// 3. Process Form Submissions (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Verify CSRF Token
    $csrf = $_POST['csrf_token'] ?? '';
    if (function_exists('verifyCSRFToken')) {
        if (!verifyCSRFToken($csrf)) {
            echo json_encode(['success' => false, 'message' => 'CSRF validation failed. Token invalid or expired.']);
            exit();
        }
    }

    // Helper: Convert values with fractions like "1½" or "1 1/2" into clean numeric numbers
    $parseFraction = function($val) {
        $val = trim((string)$val);
        if (empty($val)) return 0.00;
        if (is_numeric($val)) return (float)$val;
        if (strpos($val, '/') !== false) {
            list($num, $den) = explode('/', $val);
            if (is_numeric($num) && is_numeric($den) && $den != 0) {
                return (float)($num / $den);
            }
        }
        $val = str_replace(['½', '1/2'], '.5', $val);
        $val = str_replace(['¼', '1/4'], '.25', $val);
        $val = str_replace(['¾', '3/4'], '.75', $val);
        return (float)preg_replace('/[^0-9\.]/', '', $val);
    };

    // Helper: Format dates safely with '1900-01-01' fallback for missing birthdays
    $parseDate = function($dateVal) {
        $dateVal = trim((string)$dateVal);
        if (empty($dateVal)) return '1900-01-01';

        if (is_numeric($dateVal)) {
            $UNIX_DATE = ($dateVal - 25569) * 86400;
            return gmdate("Y-m-d", $UNIX_DATE);
        }

        if (strpos($dateVal, ' ') !== false) {
            $dateVal = explode(' ', $dateVal)[0];
        }

        $d = DateTime::createFromFormat('Y-m-d', $dateVal);
        if ($d && $d->format('Y-m-d') === $dateVal) return $dateVal;

        $d = DateTime::createFromFormat('d/m/Y', $dateVal);
        if ($d) return $d->format('Y-m-d');

        $timestamp = strtotime($dateVal);
        return $timestamp ? date('Y-m-d', $timestamp) : '1900-01-01';
    };

    // Helper: Normalize barangay names to use (Poblacion) instead of (Pob.)
    $normalizeBarangay = function($barangayStr) {
        $b = trim((string)$barangayStr);
        $b = str_replace(['(Pob.)', '(Pob)', ' Pob.'], '(Poblacion)', $b);
        return $b;
    };

    // ACTION: BATCH EXCEL UPLOAD FOR HIGH-VALUE CROPS (HVC) FARMERS MASTERLIST
    if ($action === 'upload_hvc_farmers_excel') {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid Excel file.']);
            exit();
        }

        $filePath = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $rows = [];

        if ($fileExt === 'xlsx') {
            if ($xlsx = SimpleXLSX::parse($filePath)) {
                $rows = $xlsx->rows();
            } else {
                echo json_encode(['success' => false, 'message' => 'Excel Parsing Error: ' . SimpleXLSX::parseError()]);
                exit();
            }
        } elseif ($fileExt === 'csv') {
            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 4000, ",")) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unsupported file format. Please upload a .xlsx or .csv file.']);
            exit();
        }

        $inserted = 0;
        $skipped = 0;
        $lastBarangay = '';
        $currentFarmer = null;

        try {
            $sql = "INSERT INTO hvc_farmer (
                        barangay, farmer_name, rsbsa_no, birthday, age, gender, civil_status, 
                        ave_monthly_hh_income, no_household_members, no_children, tenurial_status, 
                        hvc_crop_type, area_has, variety_used, no_hills_planted, ecosystem, 
                        annual_production_mt, annual_frequency_planting, production_per_cropping_mt, 
                        machinery_equipment_operated, remarks
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $pdo->prepare($sql);

            foreach ($rows as $index => $row) {
                if ($index < 1) continue; // Skip header row

                $row = array_map('trim', $row);

                $colBarangay = $row[0] ?? '';
                $lastName    = $row[1] ?? '';
                $firstName   = $row[2] ?? '';
                $middleName  = $row[3] ?? '';

                if (!empty($colBarangay)) {
                    $lastBarangay = $normalizeBarangay($colBarangay);
                }
                $barangay = !empty($colBarangay) ? $normalizeBarangay($colBarangay) : $lastBarangay;

                if (!empty($lastName) && strtoupper($lastName) !== 'LAST NAME') {
                    $fullName       = trim($lastName . ', ' . $firstName . ' ' . $middleName);
                    $birthday       = $parseDate($row[4] ?? '');
                    $age            = (int)($row[5] ?? 0);
                    $civilStatus    = !empty($row[6]) ? $row[6] : 'Single';
                    $rsbsaNo        = $row[7] ?? '';
                    $hhMembers      = (int)($row[8] ?? 1);
                    $children       = (int)($row[9] ?? 0);
                    $tenurialStatus = $row[10] ?? '';

                    $currentFarmer = [
                        'barangay'             => $barangay,
                        'farmer_name'          => $fullName,
                        'birthday'             => $birthday,
                        'age'                  => $age,
                        'civil_status'         => $civilStatus,
                        'rsbsa_no'             => $rsbsaNo,
                        'no_household_members' => $hhMembers,
                        'no_children'          => $children,
                        'tenurial_status'      => $tenurialStatus
                    ];
                }

                if (!$currentFarmer) {
                    $skipped++;
                    continue;
                }

                $remarks = $row[46] ?? '';

                // 1. Vegetable Columns (Cols 11 - 18)
                $vegArea      = $parseFraction($row[11] ?? 0);
                $vegCrops     = $row[12] ?? '';
                $vegHills     = (int)($row[13] ?? 0);
                $vegEco       = strtoupper($row[14] ?? '') === 'I' ? 'I' : 'R';
                $vegProdCrop  = (float)($row[15] ?? 0);
                $vegFreq      = (int)($row[16] ?? 1);
                $vegAnnual    = (float)($row[17] ?? 0);
                $vegMachinery = $row[18] ?? '';

                if (!empty($vegCrops) || $vegArea > 0 || $vegHills > 0) {
                    $stmt->execute([
                        $currentFarmer['barangay'], $currentFarmer['farmer_name'], $currentFarmer['rsbsa_no'],
                        $currentFarmer['birthday'], $currentFarmer['age'], 'Male', $currentFarmer['civil_status'],
                        0.00, $currentFarmer['no_household_members'], $currentFarmer['no_children'], $currentFarmer['tenurial_status'],
                        'Vegetables: ' . $vegCrops, $vegArea, $vegCrops, $vegHills, $vegEco,
                        $vegAnnual, $vegFreq, $vegProdCrop, $vegMachinery, $remarks
                    ]);
                    $inserted++;
                }

                // 2. Root Crops Columns (Cols 19 - 26)
                $rootArea      = $parseFraction($row[19] ?? 0);
                $rootCrops     = $row[20] ?? '';
                $rootHills     = (int)($row[21] ?? 0);
                $rootEco       = strtoupper($row[22] ?? '') === 'I' ? 'I' : 'R';
                $rootProdCrop  = (float)($row[23] ?? 0);
                $rootFreq      = (int)($row[24] ?? 1);
                $rootAnnual    = (float)($row[25] ?? 0);
                $rootMachinery = $row[26] ?? '';

                if (!empty($rootCrops) || $rootArea > 0 || $rootHills > 0) {
                    $stmt->execute([
                        $currentFarmer['barangay'], $currentFarmer['farmer_name'], $currentFarmer['rsbsa_no'],
                        $currentFarmer['birthday'], $currentFarmer['age'], 'Male', $currentFarmer['civil_status'],
                        0.00, $currentFarmer['no_household_members'], $currentFarmer['no_children'], $currentFarmer['tenurial_status'],
                        'Rootcrops: ' . $rootCrops, $rootArea, $rootCrops, $rootHills, $rootEco,
                        $rootAnnual, $rootFreq, $rootProdCrop, $rootMachinery, $remarks
                    ]);
                    $inserted++;
                }

                // 3. Fruit Trees Columns (Cols 27 - 34)
                $fruitArea     = $parseFraction($row[27] ?? 0);
                $fruitNames    = $row[28] ?? '';
                $fruitHills    = (int)($row[29] ?? 0);
                $fruitEco      = strtoupper($row[30] ?? '') === 'I' ? 'I' : 'R';
                $fruitBearing  = (float)($row[31] ?? 0);
                $fruitFreq     = (int)($row[32] ?? 1);
                $fruitAnnual   = (float)($row[33] ?? 0);
                $fruitMach     = $row[34] ?? '';

                if (!empty($fruitNames) || $fruitArea > 0 || $fruitHills > 0) {
                    $stmt->execute([
                        $currentFarmer['barangay'], $currentFarmer['farmer_name'], $currentFarmer['rsbsa_no'],
                        $currentFarmer['birthday'], $currentFarmer['age'], 'Male', $currentFarmer['civil_status'],
                        0.00, $currentFarmer['no_household_members'], $currentFarmer['no_children'], $currentFarmer['tenurial_status'],
                        'Fruit Trees: ' . $fruitNames, $fruitArea, $fruitNames, $fruitHills, $fruitEco,
                        $fruitAnnual, $fruitFreq, $fruitBearing, $fruitMach, $remarks
                    ]);
                    $inserted++;
                }

                // 4. Coconut Columns (Cols 35 - 38)
                $cocoArea   = $parseFraction($row[35] ?? 0);
                $cocoTrees  = (int)($row[36] ?? 0);
                $cocoFreq   = (int)($row[37] ?? 1);
                $cocoAnnual = (float)($row[38] ?? 0);

                if ($cocoTrees > 0 || $cocoArea > 0) {
                    $stmt->execute([
                        $currentFarmer['barangay'], $currentFarmer['farmer_name'], $currentFarmer['rsbsa_no'],
                        $currentFarmer['birthday'], $currentFarmer['age'], 'Male', $currentFarmer['civil_status'],
                        0.00, $currentFarmer['no_household_members'], $currentFarmer['no_children'], $currentFarmer['tenurial_status'],
                        'Coconut', $cocoArea, 'Coconut Trees', $cocoTrees, 'R',
                        $cocoAnnual, $cocoFreq, 0.00, '', $remarks
                    ]);
                    $inserted++;
                }

                // 5. Banana Columns (Cols 39 - 45)
                $bananaArea   = $parseFraction($row[39] ?? 0);
                $bananaVar    = $row[40] ?? '';
                $bananaHills  = (int)($row[41] ?? 0);
                $bananaEco    = strtoupper($row[42] ?? '') === 'I' ? 'I' : 'R';
                $bananaFreq   = (int)($row[43] ?? 1);
                $bananaAnnual = (float)($row[44] ?? 0);
                $bananaMach   = $row[45] ?? '';

                if (!empty($bananaVar) || $bananaArea > 0 || $bananaHills > 0) {
                    $stmt->execute([
                        $currentFarmer['barangay'], $currentFarmer['farmer_name'], $currentFarmer['rsbsa_no'],
                        $currentFarmer['birthday'], $currentFarmer['age'], 'Male', $currentFarmer['civil_status'],
                        0.00, $currentFarmer['no_household_members'], $currentFarmer['no_children'], $currentFarmer['tenurial_status'],
                        'Banana: ' . $bananaVar, $bananaArea, $bananaVar, $bananaHills, $bananaEco,
                        $bananaAnnual, $bananaFreq, 0.00, $bananaMach, $remarks
                    ]);
                    $inserted++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Successfully imported {$inserted} HVC crop records from masterlist spreadsheet." . ($skipped > 0 ? " (Skipped {$skipped} rows)" : "")
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION: BATCH EXCEL UPLOAD FOR RICE FARMERS (Single Unique Farmer + Multi-Parcel Support)
    if ($action === 'upload_rice_farmers_excel') {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid Excel file.']);
            exit();
        }

        $filePath = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $rows = [];

        if ($fileExt === 'xlsx') {
            if ($xlsx = SimpleXLSX::parse($filePath)) {
                $rows = $xlsx->rows();
            } else {
                echo json_encode(['success' => false, 'message' => 'Excel Parsing Error: ' . SimpleXLSX::parseError()]);
                exit();
            }
        } elseif ($fileExt === 'csv') {
            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unsupported file format. Please upload a .xlsx or .csv file.']);
            exit();
        }

        $farmersInserted = 0;
        $parcelsInserted = 0;
        $skipped = 0;
        $lastBarangay = '';
        $currentFarmer = null;

        try {
            // 1. Farmer Profile (Unique per RSBSA number)
            $farmerSql = "INSERT INTO rice_farmers (
                            rsbsa_no, barangay, last_name, first_name, middle_name, birthday, gender, age, 
                            civil_status, ave_monthly_income, no_household_members, no_children, 
                            hand_tractors_count, water_pumps_count, knapsack_sprayers_count, 
                            cropping_season_count, remarks
                        ) VALUES (
                            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                        ) ON DUPLICATE KEY UPDATE 
                            barangay = VALUES(barangay), last_name = VALUES(last_name), first_name = VALUES(first_name),
                            middle_name = VALUES(middle_name), birthday = VALUES(birthday), gender = VALUES(gender),
                            age = VALUES(age), civil_status = VALUES(civil_status), ave_monthly_income = VALUES(ave_monthly_income),
                            no_household_members = VALUES(no_household_members), no_children = VALUES(no_children),
                            hand_tractors_count = VALUES(hand_tractors_count), water_pumps_count = VALUES(water_pumps_count),
                            knapsack_sprayers_count = VALUES(knapsack_sprayers_count),
                            cropping_season_count = VALUES(cropping_season_count), remarks = VALUES(remarks)";
            
            $farmerStmt = $pdo->prepare($farmerSql);

            // 2. Individual Land Parcels Table
            $parcelSql = "INSERT INTO rice_parcels (
                            rsbsa_no, barangay, rice_area, tenurial_status, status_rice_area, 
                            ecosystem, preferred_variety, area_planted, area_harvested, annual_production
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $parcelStmt = $pdo->prepare($parcelSql);

            foreach ($rows as $index => $row) {
                if ($index < 2) continue; // Skip header rows

                $row = array_map('trim', $row);

                if (empty($row[1]) && empty($row[2])) continue;
                if (stristr($row[1], 'RSBSA') !== false || stristr($row[2], 'LAST NAME') !== false) continue;

                if (!empty($row[0])) {
                    $lastBarangay = $normalizeBarangay($row[0]);
                }
                $barangay = !empty($row[0]) ? $normalizeBarangay($row[0]) : $lastBarangay;

                $rsbsa     = $row[1] ?? '';
                $lastName  = $row[2] ?? '';
                $firstName = $row[3] ?? '';

                if (!empty($rsbsa) && !empty($lastName) && stripos($rsbsa, 'RSBSA') === false) {
                    $birthday        = $parseDate($row[5] ?? '');
                    $rawGender       = strtoupper($row[6] ?? '');
                    $gender          = (strpos($rawGender, 'F') !== false) ? 'Female' : 'Male';
                    $age             = (int)($row[7] ?? 0);
                    $civilStatus     = !empty($row[8]) ? $row[8] : 'Single';
                    $monthlyIncome   = (float)($row[9] ?? 0);
                    $hhMembers       = (int)($row[10] ?? 1);
                    $children        = (int)($row[11] ?? 0);
                    $handTractors    = (int)($row[17] ?? 0);
                    $waterPumps      = (int)($row[18] ?? 0);
                    $knapsackSprayers= (int)($row[19] ?? 0);
                    $rawSeason       = strtoupper($row[23] ?? '');
                    $croppingSeasons = (strpos($rawSeason, 'TWICE') !== false || $rawSeason === '2') ? 2 : 1;
                    $remarks         = $row[24] ?? '';

                    $currentFarmer = [
                        'rsbsa'             => $rsbsa,
                        'lastName'          => $lastName,
                        'firstName'         => $firstName,
                        'middleName'        => $row[4] ?? '',
                        'birthday'          => $birthday,
                        'gender'            => $gender,
                        'age'               => $age,
                        'civilStatus'       => $civilStatus,
                        'monthlyIncome'     => $monthlyIncome,
                        'hhMembers'         => $hhMembers,
                        'children'          => $children,
                        'handTractors'      => $handTractors,
                        'waterPumps'        => $waterPumps,
                        'knapsackSprayers'  => $knapsackSprayers,
                        'croppingSeasons'   => $croppingSeasons,
                        'remarks'           => $remarks
                    ];

                    $farmerStmt->execute([
                        $rsbsa, $barangay, $lastName, $firstName, $currentFarmer['middleName'],
                        $birthday, $gender, $age, $civilStatus, $monthlyIncome, $hhMembers, $children,
                        $handTractors, $waterPumps, $knapsackSprayers, $croppingSeasons, $remarks
                    ]);
                    $farmersInserted++;
                }

                $riceArea = $parseFraction($row[12] ?? 0);
                if ($currentFarmer && $riceArea > 0) {
                    $tenurialStatus   = !empty($row[13]) ? $row[13] : 'Owner';
                    $statusRiceArea   = !empty($row[14]) ? $row[14] : 'C';
                    $rawEcosystem     = strtoupper($row[15] ?? '');
                    $ecosystem        = (strpos($rawEcosystem, 'IRRIGATED') !== false || $rawEcosystem === 'I') ? 'I' : 'R';
                    $preferredVariety = !empty($row[16]) ? $row[16] : 'G';
                    $areaPlanted      = $parseFraction($row[20] ?? $riceArea);
                    $areaHarvested    = $parseFraction($row[21] ?? $riceArea);
                    $annualProduction = (float)($row[22] ?? 0);

                    $parcelStmt->execute([
                        $currentFarmer['rsbsa'],
                        $barangay,
                        $riceArea,
                        $tenurialStatus,
                        $statusRiceArea,
                        $ecosystem,
                        $preferredVariety,
                        $areaPlanted,
                        $areaHarvested,
                        $annualProduction
                    ]);

                    $parcelsInserted++;
                } else {
                    $skipped++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Successfully imported {$farmersInserted} unique farmers and {$parcelsInserted} land parcels."
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION: BATCH EXCEL UPLOAD FOR FISHERY / FISHERFOLK MASTERLIST
    if ($action === 'upload_fishery_farmers_excel') {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'Please select a valid Excel file.']);
            exit();
        }

        $filePath = $_FILES['excel_file']['tmp_name'];
        $fileName = $_FILES['excel_file']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allSheetsRows = [];

        if ($fileExt === 'xlsx') {
            if ($xlsx = SimpleXLSX::parse($filePath)) {
                foreach ($xlsx->sheetNames() as $sheetIndex => $sheetName) {
                    $allSheetsRows[$sheetName] = $xlsx->rows($sheetIndex);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Excel Parsing Error: ' . SimpleXLSX::parseError()]);
                exit();
            }
        } elseif ($fileExt === 'csv') {
            if (($handle = fopen($filePath, 'r')) !== FALSE) {
                $rows = [];
                while (($data = fgetcsv($handle, 2000, ",")) !== FALSE) {
                    $rows[] = $data;
                }
                fclose($handle);
                $allSheetsRows['Default'] = $rows;
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Unsupported file format. Please upload a .xlsx or .csv file.']);
            exit();
        }

        $inserted = 0;
        $skipped = 0;

        try {
            $sql = "INSERT INTO fishery_farmer (
                        barangay, is_active, registration_number, rsbsa_number, registration_date, 
                        last_name, first_name, middle_name, birth_date, gender, age, civil_status, 
                        ave_monthly_income, no_family_members, no_children, kind_of_fishing_activity, 
                        no_registered_fishing, no_unregistered_fishing, kind_of_fish, fish_catch_kls, 
                        fish_gears_used, kind_of_seashells, seashells_catch_kls, seashells_gears_used, 
                        seaweeds_variety, seaweeds_produced_kls, sea_cucumber_variety, sea_cucumber_catch_kls, 
                        blue_swimming_crabs_catch_kls, mangrove_crabs_catch_kls
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $pdo->prepare($sql);

            foreach ($allSheetsRows as $sheetName => $sheetRows) {
                $sheetBarangay = $normalizeBarangay($sheetName);
                $lastBarangayInSheet = $sheetBarangay;

                foreach ($sheetRows as $index => $row) {
                    if ($index < 1) continue;

                    $row = array_map('trim', $row);

                    if (empty($row[2]) && empty($row[3]) && empty($row[5])) continue;
                    if (stristr($row[2] ?? '', 'Registration') !== false || stristr($row[5] ?? '', 'Last Name') !== false) continue;

                    if (!empty($row[0])) {
                        $lastBarangayInSheet = $normalizeBarangay($row[0]);
                    }
                    $barangay = !empty($row[0]) ? $normalizeBarangay($row[0]) : $lastBarangayInSheet;

                    $isActive = strtoupper($row[1] ?? 'ACTIVE') === 'INACTIVE' ? 0 : 1;
                    $regNum = $row[2] ?? '';
                    $rsbsaNum = $row[3] ?? '';
                    $regDate = $parseDate($row[4] ?? date('Y-m-d'));

                    $lastName = $row[5] ?? '';
                    $firstName = $row[6] ?? '';
                    $middleName = $row[7] ?? '';

                    if (empty($rsbsaNum) && empty($regNum) && empty($lastName)) {
                        $skipped++;
                        continue;
                    }

                    $birthDate = $parseDate($row[8] ?? '');
                    $rawGender = strtoupper($row[9] ?? '');
                    $gender = (strpos($rawGender, 'F') !== false) ? 'Female' : 'Male';
                    $age = (int)($row[10] ?? 0);
                    $civilStatus = !empty($row[11]) ? $row[11] : 'Single';
                    $monthlyIncome = (float)($row[12] ?? 0.00);
                    $familyMembers = (int)($row[13] ?? 1);
                    $children = (int)($row[14] ?? 0);

                    $fishingActivity = $row[15] ?? '';
                    $regVessel = (int)($row[16] ?? 0);
                    $unregVessel = (int)($row[17] ?? 0);

                    $kindOfFish = $row[18] ?? '';
                    $fishCatchKls = (float)($row[19] ?? 0.00);
                    $fishGears = $row[20] ?? '';

                    $kindOfSeashells = $row[21] ?? '';
                    $seashellsCatchKls = (float)($row[22] ?? 0.00);
                    $seashellsGears = $row[23] ?? '';

                    $seaweedsVariety = $row[24] ?? '';
                    $seaweedsProducedKls = (float)($row[25] ?? 0.00);

                    $seaCucumberVariety = $row[26] ?? '';
                    $seaCucumberCatchKls = (float)($row[27] ?? 0.00);

                    $blueCrabsCatchKls = (float)($row[29] ?? 0.00);
                    $mangroveCrabsCatchKls = (float)($row[31] ?? 0.00);

                    $stmt->execute([
                        $barangay, $isActive, $regNum, $rsbsaNum, $regDate,
                        $lastName, $firstName, $middleName, $birthDate, $gender, $age, $civilStatus,
                        $monthlyIncome, $familyMembers, $children, $fishingActivity,
                        $regVessel, $unregVessel, $kindOfFish, $fishCatchKls,
                        $fishGears, $kindOfSeashells, $seashellsCatchKls, $seashellsGears,
                        $seaweedsVariety, $seaweedsProducedKls, $seaCucumberVariety, $seaCucumberCatchKls,
                        $blueCrabsCatchKls, $mangroveCrabsCatchKls
                    ]);

                    $inserted++;
                }
            }

            echo json_encode([
                'success' => true,
                'message' => "Successfully registered {$inserted} fisherfolk profiles from Excel masterlist." . ($skipped > 0 ? " (Skipped {$skipped} empty rows)" : "")
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION A: Save Monthly Yield Report
    if ($action === 'save_monthly_report') {
        $commodity = trim($_POST['commodity_type'] ?? '');
        $month = (int)($_POST['report_month'] ?? 0);
        $year = (int)($_POST['report_year'] ?? 2026);
        $yield = (float)($_POST['yield_metric_tons'] ?? 0);
        $farmers = (int)($_POST['registered_farmers_count'] ?? 0);

        if (empty($commodity) || $month < 1 || $month > 12) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all required fields properly.']);
            exit();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO commodity_reports (commodity_type, report_month, report_year, yield_metric_tons, registered_farmers_count) 
                                   VALUES (?, ?, ?, ?, ?) 
                                   ON DUPLICATE KEY UPDATE yield_metric_tons = VALUES(yield_metric_tons), registered_farmers_count = VALUES(registered_farmers_count)");
            $stmt->execute([$commodity, $month, $year, $yield, $farmers]);

            echo json_encode(['success' => true, 'message' => 'Monthly commodity report saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION B: Save Rice Farmer Registration (Individual Form)
    if ($action === 'save_rice_farmer') {
        $rsbsa_no = trim($_POST['rsbsa_no'] ?? '');
        $barangay = $normalizeBarangay($_POST['barangay'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');
        $gender = $_POST['gender'] ?? 'Male';
        $age = (int)($_POST['age'] ?? 0);
        $civil_status = $_POST['civil_status'] ?? 'Single';
        $ave_monthly_income = (float)($_POST['ave_monthly_income'] ?? 0.00);
        $no_household_members = (int)($_POST['no_household_members'] ?? 1);
        $no_children = (int)($_POST['no_children'] ?? 0);
        $rice_area = (float)($_POST['rice_area'] ?? 0.00);
        $area_planted = (float)($_POST['area_planted'] ?? 0.00);
        $area_harvested = (float)($_POST['area_harvested'] ?? 0.00);
        $annual_production = (float)($_POST['annual_production'] ?? 0.00);
        $tenurial_status = $_POST['tenurial_status'] ?? 'Owner';
        $status_rice_area = $_POST['status_rice_area'] ?? 'C';
        $ecosystem = $_POST['ecosystem'] ?? 'I';
        $preferred_variety = $_POST['preferred_variety'] ?? 'G';
        $hand_tractors_count = (int)($_POST['hand_tractors_count'] ?? 0);
        $water_pumps_count = (int)($_POST['water_pumps_count'] ?? 0);
        $knapsack_sprayers_count = (int)($_POST['knapsack_sprayers_count'] ?? 0);
        $cropping_season_count = (int)($_POST['cropping_season_count'] ?? 1);
        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($barangay) || empty($last_name) || empty($first_name)) {
            echo json_encode(['success' => false, 'message' => 'Required fields (Barangay, First & Last Name) missing.']);
            exit();
        }

        try {
            $sql = "INSERT INTO rice_farmers (
                        rsbsa_no, barangay, last_name, first_name, middle_name, birthday, gender, age, 
                        civil_status, ave_monthly_income, no_household_members, no_children, 
                        hand_tractors_count, water_pumps_count, knapsack_sprayers_count, 
                        cropping_season_count, remarks
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $rsbsa_no, $barangay, $last_name, $first_name, $middle_name, $birthday, $gender, $age,
                $civil_status, $ave_monthly_income, $no_household_members, $no_children,
                $hand_tractors_count, $water_pumps_count, $knapsack_sprayers_count,
                $cropping_season_count, $remarks
            ]);

            if ($rice_area > 0 && !empty($rsbsa_no)) {
                $parcelStmt = $pdo->prepare("INSERT INTO rice_parcels (rsbsa_no, barangay, rice_area, tenurial_status, status_rice_area, ecosystem, preferred_variety, area_planted, area_harvested, annual_production) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $parcelStmt->execute([$rsbsa_no, $barangay, $rice_area, $tenurial_status, $status_rice_area, $ecosystem, $preferred_variety, $area_planted, $area_harvested, $annual_production]);
            }

            echo json_encode(['success' => true, 'message' => 'Rice farmer record saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION C: Save Corn & Cassava Farmer Registration
    if ($action === 'save_corn_cassava') {
        $rsbsa_no = trim($_POST['rsbsa_no'] ?? '');
        $barangay = $normalizeBarangay($_POST['barangay'] ?? '');
        $farmer_name = trim($_POST['farmer_name'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');
        $age = (int)($_POST['age'] ?? 0);
        $civil_status = $_POST['civil_status'] ?? 'Single';
        $tenurial_status = trim($_POST['tenurial_status'] ?? '');
        $ave_monthly_hh_income = (float)($_POST['ave_monthly_hh_income'] ?? 0.00);
        $no_household_members = (int)($_POST['no_household_members'] ?? 1);
        $no_children = (int)($_POST['no_children'] ?? 0);

        $corn_area_has = (float)($_POST['corn_area_has'] ?? 0.00);
        $corn_variety_used = trim($_POST['corn_variety_used'] ?? '');
        $corn_no_hills_planted = (int)($_POST['corn_no_hills_planted'] ?? 0);
        $corn_ecosystem = $_POST['corn_ecosystem'] ?? '';
        $corn_annual_production_mt = (float)($_POST['corn_annual_production_mt'] ?? 0.00);
        $corn_annual_frequency_planting = (int)($_POST['corn_annual_frequency_planting'] ?? 0);
        $corn_production_per_cropping_mt = (float)($_POST['corn_production_per_cropping_mt'] ?? 0.00);
        $corn_machinery_operated = trim($_POST['corn_machinery_operated'] ?? '');

        $cassava_area_has = (float)($_POST['cassava_area_has'] ?? 0.00);
        $cassava_variety_used = trim($_POST['cassava_variety_used'] ?? '');
        $cassava_no_hills_planted = (int)($_POST['cassava_no_hills_planted'] ?? 0);
        $cassava_ecosystem = $_POST['cassava_ecosystem'] ?? '';
        $cassava_annual_production_mt = (float)($_POST['cassava_annual_production_mt'] ?? 0.00);
        $cassava_machinery_operated = trim($_POST['cassava_machinery_operated'] ?? '');

        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($barangay) || empty($farmer_name)) {
            echo json_encode(['success' => false, 'message' => 'Required fields (Barangay, Farmer Name) missing.']);
            exit();
        }

        try {
            $sql = "INSERT INTO corn_and_cassava (
                        rsbsa_no, barangay, farmer_name, birthday, age, civil_status, tenurial_status, 
                        ave_monthly_hh_income, no_household_members, no_children, corn_area_has, 
                        corn_variety_used, corn_no_hills_planted, corn_ecosystem, corn_annual_production_mt, 
                        corn_annual_frequency_planting, corn_production_per_cropping_mt, corn_machinery_operated, 
                        cassava_area_has, cassava_variety_used, cassava_no_hills_planted, cassava_ecosystem, 
                        cassava_annual_production_mt, cassava_machinery_operated, remarks
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $rsbsa_no, $barangay, $farmer_name, $birthday, $age, $civil_status, $tenurial_status,
                $ave_monthly_hh_income, $no_household_members, $no_children, $corn_area_has,
                $corn_variety_used, $corn_no_hills_planted, $corn_ecosystem, $corn_annual_production_mt,
                $corn_annual_frequency_planting, $corn_production_per_cropping_mt, $corn_machinery_operated,
                $cassava_area_has, $cassava_variety_used, $cassava_no_hills_planted, $cassava_ecosystem,
                $cassava_annual_production_mt, $cassava_machinery_operated, $remarks
            ]);

            echo json_encode(['success' => true, 'message' => 'Corn & Cassava farmer profile saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION D: Save High-Value Crops (HVC) Registration
    if ($action === 'save_hvc') {
        $rsbsa_no = trim($_POST['rsbsa_no'] ?? '');
        $barangay = $normalizeBarangay($_POST['barangay'] ?? '');
        $farmer_name = trim($_POST['farmer_name'] ?? '');
        $birthday = trim($_POST['birthday'] ?? '');
        $age = (int)($_POST['age'] ?? 0);
        $gender = $_POST['gender'] ?? 'Male';
        $civil_status = $_POST['civil_status'] ?? 'Single';
        $ave_monthly_hh_income = (float)($_POST['ave_monthly_hh_income'] ?? 0.00);
        $no_household_members = (int)($_POST['no_household_members'] ?? 1);
        $no_children = (int)($_POST['no_children'] ?? 0);
        $tenurial_status = trim($_POST['tenurial_status'] ?? '');
        $hvc_crop_type = trim($_POST['hvc_crop_type'] ?? $_POST['crop_type'] ?? '');
        $area_has = (float)($_POST['area_has'] ?? $_POST['area_planted_ha'] ?? 0.00);
        $variety_used = trim($_POST['variety_used'] ?? '');
        $no_hills_planted = (int)($_POST['no_hills_planted'] ?? 0);
        $ecosystem = trim($_POST['ecosystem'] ?? '');
        $annual_production_mt = (float)($_POST['annual_production_mt'] ?? 0.00);
        $annual_frequency_planting = (int)($_POST['annual_frequency_planting'] ?? 1);
        $production_per_cropping_mt = (float)($_POST['production_per_cropping_mt'] ?? 0.00);
        $machinery_equipment_operated = trim($_POST['machinery_equipment_operated'] ?? '');
        $remarks = trim($_POST['remarks'] ?? '');

        if (empty($barangay) || empty($farmer_name)) {
            echo json_encode(['success' => false, 'message' => 'Required fields (Barangay, Farmer Name) missing.']);
            exit();
        }

        try {
            $sql = "INSERT INTO hvc_farmer (
                        barangay, farmer_name, rsbsa_no, birthday, age, gender, civil_status, 
                        ave_monthly_hh_income, no_household_members, no_children, tenurial_status, 
                        hvc_crop_type, area_has, variety_used, no_hills_planted, ecosystem, 
                        annual_production_mt, annual_frequency_planting, production_per_cropping_mt, 
                        machinery_equipment_operated, remarks
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $barangay, $farmer_name, $rsbsa_no, $birthday, $age, $gender, $civil_status,
                $ave_monthly_hh_income, $no_household_members, $no_children, $tenurial_status,
                $hvc_crop_type, $area_has, $variety_used, $no_hills_planted, $ecosystem,
                $annual_production_mt, $annual_frequency_planting, $production_per_cropping_mt,
                $machinery_equipment_operated, $remarks
            ]);

            echo json_encode(['success' => true, 'message' => 'HVC farmer record saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION E: Save Individual Fishery Registration
    if ($action === 'save_fishery_farmer' || $action === 'save_fishery') {
        $registration_number = trim($_POST['registration_number'] ?? $_POST['fishr_no'] ?? '');
        $rsbsa_number = trim($_POST['rsbsa_number'] ?? $_POST['rsbsa_no'] ?? '');
        $registration_date = trim($_POST['registration_date'] ?? date('Y-m-d'));
        $barangay = $normalizeBarangay($_POST['barangay'] ?? '');
        
        $last_name = trim($_POST['last_name'] ?? '');
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $birth_date = trim($_POST['birth_date'] ?? '1900-01-01');
        $gender = $_POST['gender'] ?? 'Male';
        $age = (int)($_POST['age'] ?? 0);
        $civil_status = $_POST['civil_status'] ?? 'Single';

        $ave_monthly_income = (float)($_POST['ave_monthly_income'] ?? 0.00);
        $no_family_members = (int)($_POST['no_family_members'] ?? 1);
        $no_children = (int)($_POST['no_children'] ?? 0);

        $kind_of_fishing_activity = trim($_POST['kind_of_fishing_activity'] ?? '');
        $no_registered_fishing = (int)($_POST['no_registered_fishing'] ?? 0);
        $no_unregistered_fishing = (int)($_POST['no_unregistered_fishing'] ?? 0);

        $kind_of_fish = trim($_POST['kind_of_fish'] ?? '');
        $fish_catch_kls = (float)($_POST['fish_catch_kls'] ?? 0.00);
        $fish_gears_used = trim($_POST['fish_gears_used'] ?? '');

        $kind_of_seashells = trim($_POST['kind_of_seashells'] ?? '');
        $seashells_catch_kls = (float)($_POST['seashells_catch_kls'] ?? 0.00);
        $seashells_gears_used = trim($_POST['seashells_gears_used'] ?? '');

        $seaweeds_variety = trim($_POST['seaweeds_variety'] ?? '');
        $seaweeds_produced_kls = (float)($_POST['seaweeds_produced_kls'] ?? 0.00);

        $sea_cucumber_variety = trim($_POST['sea_cucumber_variety'] ?? '');
        $sea_cucumber_catch_kls = (float)($_POST['sea_cucumber_catch_kls'] ?? 0.00);

        $blue_swimming_crabs_catch_kls = (float)($_POST['blue_swimming_crabs_catch_kls'] ?? 0.00);
        $mangrove_crabs_catch_kls = (float)($_POST['mangrove_crabs_catch_kls'] ?? 0.00);

        if (empty($barangay) || empty($last_name)) {
            echo json_encode(['success' => false, 'message' => 'Required fields (Barangay, Last Name) missing.']);
            exit();
        }

        try {
            $sql = "INSERT INTO fishery_farmer (
                        barangay, is_active, registration_number, rsbsa_number, registration_date, 
                        last_name, first_name, middle_name, birth_date, gender, age, civil_status, 
                        ave_monthly_income, no_family_members, no_children, kind_of_fishing_activity, 
                        no_registered_fishing, no_unregistered_fishing, kind_of_fish, fish_catch_kls, 
                        fish_gears_used, kind_of_seashells, seashells_catch_kls, seashells_gears_used, 
                        seaweeds_variety, seaweeds_produced_kls, sea_cucumber_variety, sea_cucumber_catch_kls, 
                        blue_swimming_crabs_catch_kls, mangrove_crabs_catch_kls
                    ) VALUES (
                        ?, 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
                    )";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $barangay, $registration_number, $rsbsa_number, $registration_date,
                $last_name, $first_name, $middle_name, $birth_date, $gender, $age, $civil_status,
                $ave_monthly_income, $no_family_members, $no_children, $kind_of_fishing_activity,
                $no_registered_fishing, $no_unregistered_fishing, $kind_of_fish, $fish_catch_kls,
                $fish_gears_used, $kind_of_seashells, $seashells_catch_kls, $seashells_gears_used,
                $seaweeds_variety, $seaweeds_produced_kls, $sea_cucumber_variety, $sea_cucumber_catch_kls,
                $blue_swimming_crabs_catch_kls, $mangrove_crabs_catch_kls
            ]);

            echo json_encode(['success' => true, 'message' => 'Fishery record saved successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database Query Error: ' . $e->getMessage()]);
        }
        exit();
    }

    // ACTION F: Update Staff Profile
    if ($action === 'update_profile') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['new_password'] ?? '';

        if (empty($full_name) || empty($email)) {
            echo json_encode(['success' => false, 'message' => 'Name and email are required.']);
            exit();
        }

        try {
            if (!empty($password)) {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE staff_users SET full_name = ?, email = ?, password = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $hashed, $_SESSION['staff_id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE staff_users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->execute([$full_name, $email, $_SESSION['staff_id']]);
            }

            $_SESSION['staff_name'] = $full_name;
            $_SESSION['staff_email'] = $email;

            echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Profile Update Error: ' . $e->getMessage()]);
        }
        exit();
    }
}

// Fallback for unhandled action values
echo json_encode(['success' => false, 'message' => 'Invalid endpoint action requested.']);
exit();