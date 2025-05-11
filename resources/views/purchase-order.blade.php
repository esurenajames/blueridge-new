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
            min-width: 250px;
            display: inline-block;
            text-align: center;            
        }
        .bold {
            font-weight: bold;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <table class="main-table">
        <tr>
            <td colspan="4" style="text-align: center; border-bottom: 2px solid black;"><b>PURCHASE ORDER</b></td>
        </tr>
        <tr>
          <td colspan="2">
            <span class="bold">Barangay:</span>
            <span class="underline">Blue Ridge B</span><br>
            <span class="bold">Tel No.</span>
            <span class="underline">999-999-999</span>
          </td>
          <td colspan="2" >
            <span class="bold">City/Municipality:</span>
            <span class="underline">Quezon City</span><br>
            <span class="bold">Province:</span>
            <span class="underline">Metro manila</span>
          </td>
        </tr>
         <tr>
          <td colspan="2" style="vertical-align:top;">
            <span class="bold">Supplier:</span>
            <span class="underline">WESTERN GRAND CENTRAL<br> CO., INC.</span><br>
            <span class="bold">Address:</span>
            <span class="underline">UGF New Farmers Plaza, <br>Araneta Center,Socorro, <br>Dist.3, Quezon City</span><br>
            <span class="bold">TIN:</span>
            <span class="underline">223-359-036-00005</span>
          </td>
          <td colspan="2"  >
            <span class="bold">PO No.:</span>
            <span class="underline">2023-03-008</span><br>
            <span class="bold">Date:</span>
            <span class="underline">9-Mar-23</span>
            <span style="vertical-align: top; border-left: 2px solid black; border-right: 2px solid black;">
                <div style="text-align:left; font-weight: bold; border-bottom: 1px solid black; padding-bottom: 2px; margin-bottom: 2px;">
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
                            <div class="checkbox-container"></div>
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
            </span>
          </td>
        </tr>
        <tr>
            <td colspan="4">
                <b>Gentlemen:</b><br>
                Please deliver to this office the following articles subject to the terms and conditions contained herein
            </td>
        </tr>
        <tr>
          <td colspan="2">
            <span class="">Place of Delivery:</span>
            <span class="underline"></span><br>
            <span class="">Date of Delivery:</span>
            <span class="underline">999-999-999</span>
          </td>
          <td colspan="2" >
            <span class="">Delivery Term:</span>
            <span class="underline">Pick-Up</span><br>
            <span class="">Payment Term:</span>
            <span class="underline">Check</span>
          </td>
        </tr>
        <tr> 
            <td colspan="4">
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
                    <tr>
                        <td colspan="4" style="text-align: center;"><i>Eleven thousand nine hundred only.</i></td>
                    </tr>
                </table>
                
                <div style="text-align: center;">
                    In case of failure to make full delivery within the time specified above, a penalty of one-tenth (1/10) of 
                </div>
                
                
                <div style="text-align: right; margin-right:20%; margin-top: 20px">
                    Very truly yours,
                </div>
                
                <div>
                    <div style="text-align: right;margin-right:20px; margin-top:80px ;">
                        <div class="underline" style="text-align: center;"><b>ESPERANZA CASTRO-LEE</b></div>
                        <div style="text-align: right;margin-right:55px">Punong Barangay</div>
                    </div>
                </div>
                
                 <!-- <div style="text-align: center;">
                        <div class="underline"></div>
                        <b>Anna Francesca L. Maristela</b><br>
                        Chairman, Committee on Appropriations
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
                </div> -->
                
            </td>
        </tr>
        <tr>
          <td colspan="2">
            <div>Conforme:</div>
            <div style="text-align: center; margin-top:80px">
                        <div class="underline"></div><br>
                        (Signature over Printed Name)<br>
                        Supplier<br>
                        <div class="underline"></div><br>
                        Date
            </div>
          </td>
          <td colspan="2">
            <div>Existence of Available Appropriation:</div>
            <div style="text-align: center; margin-top:80px">
                        <div class="underline"></div><br>
                        (Signature over Printed Name)<br>
                        Chairman, Committee on Appropriation<br>
                        <div class="underline"></div><br>
                        Date
            </div>
          </td>
        </tr>
    </table>
</body>
</html>