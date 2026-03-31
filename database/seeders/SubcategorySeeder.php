<?php
namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Rapid Sales' => [
                ['Unable to login','high'],['User ID locked / inactive','high'],['Session Timeout','medium'],
                ['Login OTP','medium'],['KYC Verification issue','high'],['Connector Issue','high'],
                ['Sub Branch','medium'],['Data Sync Failure','high'],['IMD Issue','medium'],
                ['Member Management','medium'],['Documents Section','medium'],['Loan Details','medium'],
                ['Collateral Details','medium'],['Dedupe','medium'],
            ],
            'FI- Technical' => [
                ['Unable to login','high'],['User ID locked / inactive','high'],['Password reset not working','high'],
                ['Documents Upload issue','medium'],['Address capture Issue','medium'],['Lat-long fetch issue','medium'],
                ['Area Details issue','medium'],['Construction Stage issue','medium'],['Realizable value issue','medium'],
            ],
            'PSD Sales' => [
                ['Unable to login','high'],['User ID locked / inactive','high'],['Sanction Letter','medium'],
                ['Charges','medium'],['Documents Tile','medium'],['LPC','medium'],
                ['Cheque Re-payment Banking','medium'],['Repayment Mode Change','medium'],['Mandate Issue','medium'],
                ['Nomination Tile','medium'],['Sanction Condition','medium'],['Disbursement Details','medium'],
                ['Disbursement Type Change','medium'],['Beneficiary Banking','medium'],['Workflow Issue','medium'],
            ],
            'Credit' => [
                ['Unable to login','high'],['User ID locked / inactive','high'],['Deal Summary','medium'],
                ['Member Management','medium'],['Document Upload','medium'],['Loan & Charges','medium'],
                ['Collateral','medium'],['AML','high'],['PD Tile Issue','medium'],
                ['Business Verification','medium'],['Income verification','medium'],['Banking Analysis','medium'],
                ['RTR','medium'],['Final FI / Technical / Legal / RCU','medium'],['BRE / Rule Engine','medium'],
            ],
            'OPS' => [
                ['Unable to login','high'],['Deal Summary','medium'],['Charges','medium'],
                ['KYC Summary','medium'],['Document','medium'],['Sanction Condition','medium'],
                ['Disbursement Check','medium'],['Download Document','medium'],['LMS Submit','medium'],
            ],
            'LMS' => [
                ['Unable to login','high'],['User ID locked / inactive','high'],['Job Run','medium'],
                ['Data Upload failed','high'],['Payment receipt Failed','high'],['SMS Issue','medium'],
                ['Mobile Number Change','medium'],
            ],
            'Laptop' => [
                ['Battery Draining Fast','medium'],['Screen Flickering','high'],['Keyboard Malfunction','medium'],
                ['Touchpad Not Working','medium'],['Overheating','high'],['Blue Screen Error (BSOD)','critical'],
                ['Slow Boot / Startup','medium'],['WiFi Card Failure','high'],['USB Port Damage','medium'],
                ['Hinge Broken','low'],['Charging Port Issue','medium'],['Speaker/Audio Not Working','medium'],
                ['Camera Not Working','low'],['Software Installation Request','low'],['OS Re-installation Required','high'],
            ],
            'Desktop' => [
                ['Monitor No Display','high'],['Power Supply Failure','critical'],['RAM Issue / Upgrade','medium'],
                ['Hard Drive Failure','critical'],['Fan Noise / Overheating','high'],['Motherboard Issue','critical'],
                ['Graphics Card Error','high'],['Boot Loop','high'],['Peripheral Not Detected','medium'],
                ['BIOS Error','high'],['Slow Performance','medium'],['Software Installation Request','low'],
            ],
            'Printer' => [
                ['Paper Jam','low'],['Toner Low / Replacement','low'],['Print Quality Poor','low'],
                ['Network Printer Offline','medium'],['Scanner Not Working','medium'],['Driver Issue','medium'],
                ['Print Spooler Error','medium'],['Duplex Printing Failure','low'],['Printer Not Detected','medium'],
                ['Color Calibration Issue','low'],
            ],
            'Internet / Network' => [
                ['WiFi Not Connecting','high'],['Slow Internet Speed','medium'],['VPN Connection Failed','high'],
                ['Network Drive Inaccessible','high'],['IP Conflict','high'],['DNS Resolution Error','high'],
                ['Firewall Blocking Access','high'],['Proxy Configuration Error','medium'],['LAN Cable / Port Issue','medium'],
                ['Network Printer Connectivity','medium'],
            ],
            'Server' => [
                ['Server Down / Unresponsive','critical'],['High CPU / Memory Usage','high'],['Disk Space Full','high'],
                ['Backup Failure','high'],['SSL Certificate Expiry','high'],['Service Crash / Restart','critical'],
                ['Memory Leak Detected','high'],['Patch / Update Required','medium'],['Active Directory Issue','high'],
                ['File Permission Error','medium'],
            ],
            'Accessories' => [
                ['Mouse Not Working','low'],['Keyboard Damage','low'],['Headset Audio Issue','low'],
                ['Webcam Malfunction','medium'],['Docking Station Failure','medium'],['Monitor Cable Faulty','medium'],
                ['External HDD Not Detected','medium'],['Charger / Adapter Not Working','medium'],['USB Hub Issue','low'],
                ['Projector Malfunction','medium'],
            ],
            'Facility Management' => [
                ['AC Not Working / Temperature Issue','high'],['Lighting Issue / Bulb Replacement','low'],
                ['Washroom Maintenance','medium'],['Pest Control Request','low'],['Fire Safety Equipment Check','medium'],
                ['Parking Issue','low'],['Elevator Malfunction','high'],['Water Dispenser Issue','medium'],
                ['Housekeeping Request','low'],['CCTV / Security Issue','high'],['Door Lock / Access Card Issue','high'],
            ],
            'Office Supplies' => [
                ['Stationery Request','low'],['Pantry Supplies Running Low','low'],['Furniture Repair / Replacement','medium'],
                ['Whiteboard / Marker Request','low'],['ID Card Issue / Replacement','medium'],
                ['Visiting Card Printing','low'],['Letterhead / Envelope Printing','low'],
            ],
            'Travel & Transport' => [
                ['Cab Booking Issue','medium'],['Travel Reimbursement Delay','medium'],['Hotel Booking Request','low'],
                ['Visa Assistance Request','medium'],['Flight Booking / Change','medium'],
                ['Travel Policy Clarification','low'],['Travel Insurance Query','low'],
            ],
            'HR Queries' => [
                ['Leave Balance Query','low'],['Payslip Issue / Discrepancy','high'],['PF / ESI Query','medium'],
                ['Mediclaim / Health Insurance','medium'],['Offer Letter / Experience Letter','medium'],
                ['Attendance Correction Request','medium'],['Policy Clarification','low'],
                ['Salary Revision Query','medium'],['Tax Declaration / Investment Proof','medium'],['Training Request','low'],
            ],
            'Onboarding / Offboarding' => [
                ['New Joinee Setup / Induction','high'],['Asset Handover / Collection','medium'],['Exit Formalities','medium'],
                ['Access Revocation Request','high'],['Knowledge Transfer Planning','medium'],
                ['Final Settlement Query','medium'],['Relieving Letter Request','medium'],
            ],
        ];

        foreach ($data as $categoryName => $subcats) {
            $category = Category::where('name', $categoryName)->first();
            if (!$category) continue;
            foreach ($subcats as $i => [$name, $priority]) {
                Subcategory::create([
                    'category_id'      => $category->id,
                    'name'             => $name,
                    'default_priority' => $priority,
                    'is_active'        => true,
                    'sort_order'       => $i + 1,
                ]);
            }
        }
    }
}
