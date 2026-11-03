<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RSBSA Enrollment Form - Complete</title>
  <style>
    :root {
      --primary-green: #1b5e20;
      --accent-green: #2e7d32;
      --light-bg: #f4f6f8;
      --border-color: #000;
    }

    * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      background-color: var(--light-bg);
      margin: 0;
      padding: 20px;
    }

    .form-container {
      max-width: 950px;
      margin: 0 auto;
      background: #fff;
      padding: 25px;
      border: 2px solid var(--border-color);
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }

    .header-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }

    .header-table td {
      border: 1px solid var(--border-color);
      padding: 8px;
      vertical-align: middle;
    }

    .logo-container {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .logo-container img {
      width: 60px;
      height: 60px;
      object-fit: contain;
    }

    .header-title {
      text-align: center;
    }

    .header-title h3 { margin: 2px 0; font-size: 13px; }
    .header-title h2 { margin: 2px 0; font-size: 15px; color: var(--primary-green); }
    .header-title h1 { margin: 5px 0; font-size: 18px; text-transform: uppercase; }

    .photo-box {
      width: 110px;
      height: 110px;
      border: 1px dashed #000;
      text-align: center;
      font-size: 9px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      margin: 0 auto;
    }

    .instruction-box {
      background: #f0f0f0;
      padding: 8px;
      font-size: 11px;
      font-weight: bold;
      border: 1px solid var(--border-color);
      margin-bottom: 15px;
    }

    .section-header {
      background-color: #000;
      color: white;
      padding: 6px 10px;
      font-weight: bold;
      font-size: 13px;
      margin-top: 20px;
      margin-bottom: 0;
      text-transform: uppercase;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(12, 1fr);
      gap: 10px;
      margin-top: 10px;
      margin-bottom: 10px;
    }

    .col-12 { grid-column: span 12; }
    .col-8  { grid-column: span 8; }
    .col-6  { grid-column: span 6; }
    .col-4  { grid-column: span 4; }
    .col-3  { grid-column: span 3; }
    .col-2  { grid-column: span 2; }

    .field-group {
      display: flex;
      flex-direction: column;
    }

    .field-group label {
      font-size: 10px;
      font-weight: bold;
      margin-bottom: 3px;
      text-transform: uppercase;
    }

    .field-group input[type="text"],
    .field-group input[type="date"],
    .field-group select,
    .field-group input[type="file"] {
      padding: 6px;
      border: 1px solid #777;
      border-radius: 2px;
      font-size: 11px;
      width: 100%;
    }

    .checkbox-group {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      font-size: 12px;
      margin-top: 10px;
    }

    .checkbox-item {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .parcel-card {
      border: 1px solid var(--border-color);
      padding: 10px;
      margin-top: 10px;
      margin-bottom: 15px;
      background: #fafafa;
    }

    .parcel-header {
      font-weight: bold;
      font-size: 13px;
      border-bottom: 1px solid #ccc;
      padding-bottom: 5px;
      margin-bottom: 10px;
      color: var(--primary-green);
    }

    .reference-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      margin-top: 15px;
      margin-bottom: 15px;
    }

    .reference-table td, .reference-table th {
      border: 1px solid #ccc;
      padding: 6px;
      vertical-align: top;
    }

    .reference-table th {
      background: #e8f5e9;
      text-align: left;
    }

    .stub-container {
      border: 2px dashed #000;
      padding: 15px;
      margin-top: 25px;
      background: #fff8e1;
    }

    .page-break {
      margin: 35px 0;
      border-top: 2px dashed #888;
      position: relative;
      text-align: center;
    }

    .page-break::after {
      content: "PAGE 2";
      background: #fff;
      padding: 0 10px;
      position: relative;
      top: -10px;
      font-size: 12px;
      font-weight: bold;
      color: #666;
    }

    /* PART 4 SPECIFIC STYLES */
    .part4-table {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid var(--border-color);
    }

    .part4-table td, .part4-table th {
      border: 1px solid var(--border-color);
      padding: 8px;
      font-size: 11px;
    }

    .declaration-text {
      background: #f9f9f9;
      font-size: 11px;
      line-height: 1.4;
      text-align: justify;
    }

    .field-header {
      background: #e0e0e0;
      text-align: center;
      font-weight: bold;
      font-size: 10px;
      text-transform: uppercase;
      padding: 4px;
    }

    .verification-section {
      margin-top: 15px;
    }

    .verification-title {
      font-weight: bold;
      font-size: 11px;
      margin-bottom: 10px;
      text-transform: uppercase;
    }

    .sig-line-container {
      text-align: center;
      vertical-align: top;
      padding-top: 25px !important;
    }

    .sig-line {
      border-top: 1px solid #000;
      margin-top: 5px;
      padding-top: 3px;
      font-size: 9px;
      font-weight: bold;
      line-height: 1.2;
    }

    .sig-line span.required {
      font-style: italic;
      font-weight: normal;
      display: block;
    }

    .privacy-box {
      border: 1px solid var(--border-color);
      margin-top: 15px;
    }

    .privacy-header {
      background: #e0e0e0;
      text-align: center;
      font-weight: bold;
      font-size: 11px;
      padding: 5px;
      text-transform: uppercase;
      border-bottom: 1px solid var(--border-color);
    }

    .privacy-body {
      padding: 8px;
      font-size: 9.5px;
      line-height: 1.35;
      text-align: justify;
    }

    .submit-btn {
      background-color: var(--primary-green);
      color: white;
      border: none;
      padding: 14px 25px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      width: 100%;
      margin-top: 20px;
      border-radius: 3px;
    }

    .submit-btn:hover {
      background-color: var(--accent-green);
    }

    @media (max-width: 768px) {
      .col-8, .col-6, .col-4, .col-3, .col-2 {
        grid-column: span 12;
      }
      .part4-table td {
        display: block;
        width: 100% !important;
      }
    }
  </style>
</head>
<body>

<div class="form-container">
  <form id="rsbsaForm" action="#" method="POST" enctype="multipart/form-data">
    
    <!-- PAGE 1 HEADER -->
    <table class="header-table">
      <tr>
        <td style="width: 20%;">
          <div class="logo-container">
            <img src="image\rsbsa logo.jpg" alt="DA Logo">
          </div>
        </td>
        <td class="header-title" style="width: 60%;">
          <h3>DEPARTMENT OF AGRICULTURE</h3>
          <h2>REGISTRY SYSTEM FOR BASIC SECTORS IN AGRICULTURE</h2>
          <h1>RSBSA Enrollment Form</h1>
        </td>
        <td style="width: 20%; text-align: center;">
          <div class="photo-box">
            2x2 PICTURE<br><br>
            PHOTO TAKEN WITHIN 6 MONTHS
          </div>
        </td>
      </tr>
    </table>

    <div class="form-grid">
      <div class="field-group col-6">
        <label>Transaction Code:</label>
        <input type="text" name="transaction_code" placeholder="System Generated" readonly>
      </div>
      <div class="field-group col-6">
        <label>PhilID/ePhilID Status</label>
        <select name="philid_status">
          <option value="With PhilID">With PhilID/ePhilID (PCN)</option>
          <option value="No PhilID">No PhilID/ePhilID (TRN)</option>
        </select>
      </div>
    </div>

    <div class="instruction-box">
      INSTRUCTIONS (PANUTO): Write in CAPITAL LETTERS. Put X on the box of your answer. (Magsulat gamit ang MALALAKING LETRA. Lagyan ng X ang kahon ng iyong sagot.)
    </div>

    <!-- PART 1: PERSONAL INFORMATION -->
    <div class="section-header">Part 1: Personal Information</div>

    <div class="form-grid">
      <div class="field-group col-3">
        <label>Surname (Apelyido)</label>
        <input type="text" name="surname" required>
      </div>
      <div class="field-group col-3">
        <label>First Name (Pangalan)</label>
        <input type="text" name="firstname" required>
      </div>
      <div class="field-group col-3">
        <label>Middle Name (Gitnang Pangalan)</label>
        <input type="text" name="middlename">
      </div>
      <div class="field-group col-3">
        <label>Extension Name (e.g. Jr., Sr.)</label>
        <input type="text" name="ext_name">
      </div>
    </div>

    <div class="form-grid">
      <div class="field-group col-3">
        <label>Sex (Kasarian)</label>
        <select name="sex" required>
          <option value="">Select</option>
          <option value="Male">Male (Lalaki)</option>
          <option value="Female">Female (Babae)</option>
        </select>
      </div>
      <div class="field-group col-3">
        <label>Date of Birth (Petsa ng Kapanganakan)</label>
        <input type="date" name="dob" required>
      </div>
      <div class="field-group col-3">
        <label>Place of Birth (Lugar ng Kapanganakan)</label>
        <input type="text" name="place_of_birth">
      </div>
      <div class="field-group col-3">
        <label>Mobile Number</label>
        <input type="text" name="mobile_no" placeholder="09XXXXXXXXX" pattern="[0-9]{11}" required>
      </div>
    </div>

    <div class="form-grid">
      <div class="field-group col-12">
        <label>Permanent Address (Permanenteng Tirahan)</label>
        <input type="text" name="permanent_address" placeholder="House/Lot/Bldg No., Street/Sitio/Subdivision, Barangay, City/Municipality, Province, Region" required>
      </div>
    </div>

    <div class="form-grid">
      <div class="field-group col-12">
        <label>Mother's Maiden Name (Pangalan ng Ina sa Pagkadalaga)</label>
        <input type="text" name="mothers_maiden_name" placeholder="First Name, Middle Name, Surname">
      </div>
    </div>

    <div class="form-grid">
      <div class="field-group col-4">
        <label>Civil Status (Katayuang Sibil)</label>
        <select name="civil_status">
          <option value="Single">Single (Walang asawa)</option>
          <option value="Married">Married (Kasal)</option>
          <option value="Widow/er">Widow/er (Balo)</option>
          <option value="Legally Separated">Legally Separated (Hiwalay)</option>
        </select>
      </div>
      <div class="field-group col-4">
        <label>Highest Formal Education</label>
        <select name="education">
          <option value="None">None</option>
          <option value="Pre-school">Pre-school</option>
          <option value="Elementary">Elementary</option>
          <option value="Junior High School">Junior High School (K-12)</option>
          <option value="Senior High School">Senior High School (K-12)</option>
          <option value="High School">High School (non K-12)</option>
          <option value="College">College</option>
          <option value="Vocational">Vocational</option>
          <option value="Post-graduate">Post-graduate</option>
        </select>
      </div>
      <div class="field-group col-4">
        <label>Religion (Relihiyon)</label>
        <select name="religion">
          <option value="Christianity">Christianity</option>
          <option value="Islam">Islam</option>
          <option value="Others">Others</option>
          <option value="None">None</option>
        </select>
      </div>
    </div>

    <div class="form-grid">
      <div class="field-group col-4">
        <label>Part of IP/ICC Community?</label>
        <select name="is_ip">
          <option value="No">No</option>
          <option value="Yes">Yes</option>
        </select>
      </div>
      <div class="field-group col-4">
        <label>Person With Disability (PWD)?</label>
        <select name="is_pwd">
          <option value="No">No</option>
          <option value="Yes">Yes</option>
        </select>
      </div>
      <div class="field-group col-4">
        <label>4Ps Beneficiary?</label>
        <select name="is_4ps">
          <option value="No">No</option>
          <option value="Yes">Yes</option>
        </select>
      </div>
    </div>

    <!-- PART 2: LIVELIHOOD PROFILE -->
    <div class="section-header">Part 2: Livelihood Profile</div>
    <div class="checkbox-group">
      <div class="checkbox-item">
        <input type="checkbox" id="farmer" name="livelihood[]" value="Farmer">
        <label for="farmer">FARMER (Magsasaka)</label>
      </div>
      <div class="checkbox-item">
        <input type="checkbox" id="farmworker" name="livelihood[]" value="Farm Worker">
        <label for="farmworker">FARM WORKER (Manggagawa sa Sakahan)</label>
      </div>
      <div class="checkbox-item">
        <input type="checkbox" id="fisher" name="livelihood[]" value="Fisherfolk">
        <label for="fisher">FISHER (Mangingisda)</label>
      </div>
      <div class="checkbox-item">
        <input type="checkbox" id="agriyouth" name="livelihood[]" value="Agri-Youth">
        <label for="agriyouth">AGRI-YOUTH</label>
      </div>
    </div>

    <!-- ENROLLMENT STUB (PAGE 1 BOTTOM) -->
    <div class="stub-container">
      <div style="text-align: center; font-weight: bold; font-size: 12px; margin-bottom: 10px;">
        RSBSA Enrollment Stub: Client's Copy
      </div>
      <div class="form-grid">
        <div class="field-group col-8">
          <label>Name of Enrollee:</label>
          <input type="text" name="stub_name" placeholder="First Name, Middle Name, Surname, Ext. Name">
        </div>
        <div class="field-group col-4">
          <label>Transaction Code:</label>
          <input type="text" name="stub_tx_code" placeholder="System generated" readonly>
        </div>
      </div>
    </div>

    <!-- PAGE BREAK INDICATOR -->
    <div class="page-break"></div>

    <!-- PAGE 2 HEADER -->
    <table class="header-table">
      <tr>
        <td style="width: 20%;">
          <div class="logo-container">
            <img src="https://via.placeholder.com/60?text=DA+Logo" alt="DA Logo">
          </div>
        </td>
        <td class="header-title" style="width: 80%;">
          <h3>DEPARTMENT OF AGRICULTURE</h3>
          <h2>REGISTRY SYSTEM FOR BASIC SECTORS IN AGRICULTURE</h2>
          <h1>RSBSA Enrollment Form - Page 2</h1>
        </td>
      </tr>
    </table>

    <!-- PART 3: FARM PARCEL INFORMATION -->
    <div class="section-header">Part 3: Farm Parcel Information</div>
    
    <!-- FARM PARCEL 1 -->
    <div class="parcel-card">
      <div class="parcel-header">Farm Parcel #1</div>
      <div class="form-grid">
        <div class="field-group col-6">
          <label>Farm Location (Barangay, City/Municipality, Province)</label>
          <input type="text" name="parcel_location_1">
        </div>
        <div class="field-group col-3">
          <label>Total Area (Ha)</label>
          <input type="text" name="parcel_area_1">
        </div>
        <div class="field-group col-3">
          <label>Tenure Type</label>
          <select name="tenure_type_1">
            <option value="Registered Owner">Registered Owner</option>
            <option value="Tenant">Tenant</option>
            <option value="Lessee">Lessee</option>
            <option value="Others">Others</option>
          </select>
        </div>
      </div>
      <div class="form-grid">
        <div class="field-group col-3">
          <label>Commodity</label>
          <input type="text" name="commodity_1">
        </div>
        <div class="field-group col-3">
          <label>Farm Type Code (1-4)</label>
          <input type="text" name="farm_type_1" placeholder="See reference below">
        </div>
        <div class="field-group col-3">
          <label>Organic Agri? (Y/N)</label>
          <select name="organic_1">
            <option value="N">No</option>
            <option value="Y">Yes</option>
          </select>
        </div>
        <div class="field-group col-3">
          <label>Cropping Schedule</label>
          <input type="text" name="schedule_1" placeholder="Ex: Jan-Mar">
        </div>
      </div>
      <div class="form-grid">
        <div class="field-group col-6">
          <label>Name of Land Owner (if not owner)</label>
          <input type="text" name="land_owner_1">
        </div>
        <div class="field-group col-3">
          <label>Ancestral Domain (AD)?</label>
          <select name="ad_1"><option value="No">No</option><option value="Yes">Yes</option></select>
        </div>
        <div class="field-group col-3">
          <label>Agrarian Reform Beneficiary?</label>
          <select name="arb_1"><option value="No">No</option><option value="Yes">Yes</option></select>
        </div>
      </div>
    </div>

    <!-- FARM PARCEL 2 -->
    <div class="parcel-card">
      <div class="parcel-header">Farm Parcel #2</div>
      <div class="form-grid">
        <div class="field-group col-6">
          <label>Farm Location (Barangay, City/Municipality, Province)</label>
          <input type="text" name="parcel_location_2">
        </div>
        <div class="field-group col-3">
          <label>Total Area (Ha)</label>
          <input type="text" name="parcel_area_2">
        </div>
        <div class="field-group col-3">
          <label>Tenure Type</label>
          <select name="tenure_type_2">
            <option value="Registered Owner">Registered Owner</option>
            <option value="Tenant">Tenant</option>
            <option value="Lessee">Lessee</option>
            <option value="Others">Others</option>
          </select>
        </div>
      </div>
      <div class="form-grid">
        <div class="field-group col-3">
          <label>Commodity</label>
          <input type="text" name="commodity_2">
        </div>
        <div class="field-group col-3">
          <label>Farm Type Code (1-4)</label>
          <input type="text" name="farm_type_2">
        </div>
        <div class="field-group col-3">
          <label>Organic Agri? (Y/N)</label>
          <select name="organic_2">
            <option value="N">No</option>
            <option value="Y">Yes</option>
          </select>
        </div>
        <div class="field-group col-3">
          <label>Cropping Schedule</label>
          <input type="text" name="schedule_2">
        </div>
      </div>
    </div>

    <!-- REFERENCE LISTS -->
    <table class="reference-table">
      <tr>
        <th>ACCEPTED PROOF OF IDENTITY</th>
        <th>ACCEPTED PROOF OF LAND OWNERSHIP / AGREEMENT</th>
        <th>FARM TYPE CODES</th>
      </tr>
      <tr>
        <td>
          • PhilID / National ID / ePhilID<br>
          • Passport | Driver's License<br>
          • UMID | SSS ID | PRC ID<br>
          • Voter's ID | Senior Citizen ID<br>
          • Barangay ID | Postal ID
        </td>
        <td>
          (a) Certificate of Land Transfer<br>
          (b) Emancipation Patent<br>
          (c) CLOA (Individual/Collective)<br>
          (i) Certificate of Title<br>
          (l) Tax Declaration
        </td>
        <td>
          1 - Irrigated<br>
          2 - Rainfed Upland<br>
          3 - Rainfed Lowland<br>
          4 - Urban/Peri-Urban<br>
          N/A - Not Applicable
        </td>
      </tr>
    </table>

    <!-- PART 4: CONSENT FORM AND DATA PRIVACY NOTICE -->
    <div class="section-header">PART 4: CONSENT FORM AND DATA PRIVACY NOTICE</div>

    <table class="part4-table">
      <!-- Declaration Row -->
      <tr>
        <td colspan="3" class="declaration-text">
          I hereby declare that all information indicated in this form are true, correct and complete, and that they may be used by the Department of Agriculture for the purposes of registration to the RSBSA and other legitimate interests of the Department pursuant to its mandates. I am fully aware that I can be held liable for any misdeclaration or intentional omission made herein pursuant to applicable laws and regulations.<br><br>
          Furthermore, I hereby give consent to the Department of Agriculture to conduct validation activities on my declared farm parcels through the RSBSA Georeferencing Activity.
        </td>
      </tr>

      <!-- Inputs Row -->
      <tr>
        <td style="width: 25%; vertical-align: bottom;">
          <input type="date" name="registrant_date" required style="width: 100%; box-sizing: border-box;">
        </td>
        <td style="width: 40%; vertical-align: bottom;">
          <input type="text" name="registrant_printed_name" placeholder="Enter Full Name" required style="width: 100%; box-sizing: border-box;">
        </td>
        <td style="width: 35%; vertical-align: bottom;">
          <input type="file" name="registrant_signature" accept="image/*" style="width: 100%; box-sizing: border-box;">
        </td>
      </tr>

      <!-- Sub-headers Row -->
      <tr>
        <td class="field-header">DATE</td>
        <td class="field-header">PRINTED NAME OF REGISTRANT</td>
        <td class="field-header">SIGNATURE / THUMBMARK</td>
      </tr>
    </table>

    <!-- VERIFIED TRUE AND CORRECT BY SECTION -->
    <div class="verification-section">
      <div class="verification-title">VERIFIED TRUE AND CORRECT BY:</div>

      <table class="part4-table">
        <tr>
          <!-- Official 1 -->
          <td class="sig-line-container" style="width: 25%;">
            <input type="file" name="sig_official_1" accept="image/*" style="font-size: 9px; margin-bottom: 5px;">
            <div class="sig-line">
              Barangay Chairperson / IA President / ICC/IPS Leader/Elder (IPs) / C/Mun Veterinarian (Livestock) / Mill District Officer (Sugarcane) / C/MARO (ARBs)
            </div>
          </td>

          <!-- Official 2 -->
          <td class="sig-line-container" style="width: 25%;">
            <input type="file" name="sig_official_2" accept="image/*" style="font-size: 9px; margin-bottom: 5px;">
            <div class="sig-line">
              City/Municipal Agri-Fishery Council (C/MAFC)<br>Chairperson
            </div>
          </td>

          <!-- Official 3 -->
          <td class="sig-line-container" style="width: 25%;">
            <input type="file" name="sig_official_3" accept="image/*" style="font-size: 9px; margin-bottom: 5px;">
            <div class="sig-line">
              City/Municipal Agriculturist (C/MA)
              <span class="required">required</span>
            </div>
          </td>

          <!-- Official 4 -->
          <td class="sig-line-container" style="width: 25%;">
            <input type="file" name="sig_official_4" accept="image/*" style="font-size: 9px; margin-bottom: 5px;">
            <div class="sig-line">
              Enumerator (if administered)
              <span class="required">required</span>
            </div>
          </td>
        </tr>
      </table>
    </div>

    <!-- DATA PRIVACY NOTICE BOX -->
    <div class="privacy-box">
      <div class="privacy-header">DATA PRIVACY NOTICE</div>
      <div class="privacy-body">
        The Department of Agriculture (DA) commits to uphold your rights to privacy as a data subject under the Data Privacy Act of 2012 (DPA)[cite: 1]. In this regard, the DA shall strictly implement controls and measures compliant to the DPA, its IRR, and the Circulars issued by the National Privacy Commission[cite: 1]. All personal information collected through this Form shall be used for purposes of documentation, planning, and policy-making, reporting and other connected processes in availing of agri-fishery related interventions[cite: 1]. Your information is retained for the duration necessary for the aforesaid purposes, complying with applicable laws and regulations[cite: 1]. Personal data processed is not shared with any other party, unless such disclosure is allowed under the DPA[cite: 1]. As a data subject, you have the right to reasonable access to personal data, correction of inaccuracies, deletion of information, objection to processing, data portability, claim for compensation for harm caused by misuse, and the option to file a complaint with the NPC for violation of privacy rights[cite: 1].<br>
        <div style="text-align: center; margin-top: 4px; font-weight: bold;">
          For any data privacy-related concerns, you may contact the Data Privacy Officer at dpo@da.gov.ph.
        </div>
      </div>
    </div>

    <!-- SECOND VERIFICATION BLOCK (FOOTER) -->
    <div style="margin-top: 15px;">
      <table class="part4-table">
        <tr>
          <td class="sig-line-container" style="width: 25%;">
            <div class="sig-line" style="border-top: none;">
              Barangay Chairperson / IA President / ICC/IPS Leader/Elder (IPs) / C/Mun Veterinarian (Livestock) / Mill District Officer (Sugarcane) / C/MARO (ARBs)
            </div>
          </td>
          <td class="sig-line-container" style="width: 25%;">
            <div class="sig-line" style="border-top: none;">
              City/Municipal Agri-Fishery Council (C/MAFC)<br>Chairperson
            </div>
          </td>
          <td class="sig-line-container" style="width: 25%;">
            <div class="sig-line" style="border-top: none;">
              City/Municipal Agriculturist (C/MA)
              <span class="required">required</span>
            </div>
          </td>
          <td class="sig-line-container" style="width: 25%;">
            <div class="sig-line" style="border-top: none;">
              Enumerator (if administered)
              <span class="required">required</span>
            </div>
          </td>
        </tr>
      </table>
    </div>

    <button type="submit" class="submit-btn">SUBMIT COMPLETE RSBSA FORM</button>
  </form>
</div>

</body>
</html>