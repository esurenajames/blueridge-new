<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
        }
        .main-table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
        }
        .main-table td {
            border: 1px solid black;
            padding: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table td {
            border: 1px solid black;
            padding: 5px;
            height: 25px;
        }
        input[type="text"] {
            width: 95%;
            border: none;
            border-bottom: 1px solid #999;
            outline: none;
        }
        .checkbox-container {
            display: inline-block;
            border: 1px solid black;
            width: 15px;
            height: 15px;
            margin: 0 5px;
        }
        .checkbox-checked {
            background-color: #000;
        }
        .underline {
            border-bottom: 1px solid black;
            min-width: 150px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <table class="main-table">
        <tr>
            <td colspan="4" style="text-align: center; border-bottom: 2px solid black;"><b>PURCHASE ORDER</b></td>
        </tr>
        <tr>
            <td>Barangay:</td>
            <td>BLUE RIDGE II</td>
            <td>City/Municipality:</td>
            <td>QUEZON CITY</td>
        </tr>
        <tr>
            <td>Tel. No.:</td>
            <td>(8)633-9622</td>
            <td>Province:</td>
            <td>METRO MANILA</td>
        </tr>
        <tr>
            <td>Supplier:</td>
            <td>WESTERN GRAND CENTRAL CO., INC.</td>
            <td>PO No.:</td>
            <td>2023-03-008</td>
        </tr>
        <tr>
            <td>Address:</td>
            <td>UGF New Farmers Plaza, Araneta Center,<br>Socorro, Dist.3, Quezon City</td>
            <td>Date:</td>
            <td>9-Mar-23</td>
        </tr>
        <tr>
            <td>TIN:</td>
            <td>223-359-036-00005</td>
            <td rowspan="2" style="vertical-align: top; border-left: 2px solid black; border-right: 2px solid black;">
                <div style="text-align: center; font-weight: bold; border-bottom: 1px solid black; padding-bottom: 2px; margin-bottom: 2px;">
                    Mode of Procurement:
                </div>
                <table style="width: 100%; border-collapse: collapse; text-align: center;">
                    <tr>
                        <td style="border: 1px solid black; width: 33%;">
                            <div class="checkbox-container"></div>
                            <div style="font-size: 11px;">Bidding</div>
                        </td>
                        <td style="border: 1px solid black; width: 33%;">
                            <div class="checkbox-container"></div>
                            <div style="font-size: 11px;">Negotiated</div>
                        </td>
                        <td style="border: 1px solid black; width: 33%;">
                            <div class="checkbox-container"></div>
                            <div style="font-size: 11px;">Over the Counter</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid black;">
                            <div class="checkbox-container checkbox-checked"></div>
                            <div style="font-size: 11px;">Shopping</div>
                        </td>
                        <td style="border: 1px solid black;">
                            <div class="checkbox-container"></div>
                            <div style="font-size: 11px;">Repeat Order</div>
                        </td>
                        <td style="border: 1px solid black;">
                            <div class="checkbox-container"></div>
                            <div style="font-size: 11px;">Limited Source</div>
                        </td>
                    </tr>
                </table>
            </td>
            <td rowspan="2"></td>
        </tr>
        <tr>
            <td colspan="2"></td>
        </tr>
        <tr>
            <td colspan="2">
                <b>Gentlemen:</b><br>
                Please deliver to this office the following articles subject to the terms and conditions contained herein
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <div>Place of Delivery: <span class="underline"></span></div>
                    <div>Delivery Term: <span class="underline">Pick-Up</span></div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <div>Date of Delivery: <span class="underline"></span></div>
                    <div>Payment Term: <span class="underline">Check</span></div>
                </div>
                
                <table class="items-table">
                    <tr>
                        <td style="width: 50%">Particulars</td>
                        <td>Quantity</td>
                        <td>Unit Cost</td>
                        <td>Amount</td>
                    </tr>
                    <tr>
                        <td>KCS222 Konzert Micro Compo Sys</td>
                        <td>1</td>
                        <td>₱7,000.00</td>
                        <td>₱7,000.00</td>
                    </tr>
                    <tr>
                        <td>Astra Megasound SD Karaoke Player</td>
                        <td>1</td>
                        <td>₱4,900.00</td>
                        <td>₱4,900.00</td>
                    </tr>
                    <tr>
                        <td>XXfreebm23 BNB Free Microphone</td>
                        <td>1</td>
                        <td>₱0.00</td>
                        <td>₱0.00</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right;"><b>TOTAL=</b></td>
                        <td><b>₱11,900.00</b></td>
                    </tr>
                </table>
                
                <div style="margin: 10px 0;">
                    <i>Eleven thousand nine hundred only.</i>
                </div>
                
                <div>
                    In case of failure to make full delivery within the time specified above, a penalty of one-tenth (1/10) of
                </div>
                
                <div style="text-align: right; margin: 20px 0;">
                    Very truly yours,
                </div>
                
                <div style="display: flex; justify-content: space-between; margin: 40px 0;">
                    <div style="text-align: center;">
                        <div class="underline"></div>
                        <b>ESPERANZA CASTRO-LEE</b><br>
                        Punong Barangay
                    </div>
                    <div style="text-align: center;">
                        <div class="underline"></div>
                        <b>Anna Francesca L. Maristela</b><br>
                        Chairman, Committee on Appropriations
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between;">
                    <div>
                        Conforme:<br>
                        <div class="underline"></div>
                        (Signature over Printed Name)<br>
                        Supplier<br>
                        <div class="underline"></div>
                        Date
                    </div>
                    <div>
                        Existence of Available Appropriation:<br>
                        <div class="underline"></div>
                        Date
                    </div>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>