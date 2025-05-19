<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Abstract of Canvass</title>
    <style>
        @page {
            size: A4 landscape;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            margin: 0;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .location {
            text-align: left;
            margin: 0; /* Removed margin-top and margin-bottom */
            font-weight: bold;
        }
        .location p {
            margin: 0; /* Remove margins from p tags inside location */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 4px;
            font-size: 9pt;
            vertical-align: middle;
        }
        th {
            font-weight: bold;
            text-align: center;
        }
        td.item-center {
            text-align: center;
        }
        td.item-description {
            text-align: center;
        }
        td.price {
            text-align: right;
        }
        .company-header {
            text-align: center;
            font-weight: bold;
            vertical-align: top;
        }
        .company-info {
            font-size: 8pt;
            text-align: center;
        }
        .total-row td {
            font-weight: bold;
        }
        .certification {
            font-size: 9pt;
            margin-top: 0; /* Removed margin-top */
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
        }
        .signature-block {
            float: left;
            width: 33%;
            text-align: center;
        }
        .signature-name {
            margin-top: 25px;
            border-top: 1px solid black;
            display: inline-block;
            padding-top: 5px;
            min-width: 150px;
        }
        .signature-title {
            font-size: 9pt;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
        .peso-sign {
            font-family: DejaVu Sans, Arial, sans-serif;
        }
        .quotations-row {
            text-align: center;
            font-weight: bold;
        }
        .award-column {
            font-weight: bold;
            text-align: center;
        }
        .awarded-company {
            font-size: 10pt;
            text-align: center;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <div class="header">
        <p>ABSTRACT OF CANVASS</p>
    </div>
    
    <div class="location">
        <p>BARANGAY BLUE RIDGE B</p>
        <p>QUEZON CITY, DISTRICT III</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 5%" rowspan="2">ITEM</th>
                <th style="width: 5%" rowspan="2">QTY.</th>
                <th style="width: 10%" rowspan="2">UNIT</th>
                <th style="width: 25%" rowspan="2">DESCRIPTION OF ARTICLES</th>
                <th colspan="{{ count($companies) }}" class="quotations-row">QUOTATIONS</th>
                <th style="width: 15%" rowspan="2" class="award-column">AWARDED TO:</th>
            </tr>
            <tr>
                @foreach($companies as $company)
                    <th class="company-header">
                        {{ strtoupper($company->company_name) }}<br>
                        <span class="company-info">{{ $company->address }}</span>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($items as $index => $item)
                <tr>
                    <td class="item-center">{{ $loop->iteration }}</td>
                    <td class="item-center">{{ $item['quantity'] }}</td>
                    <td class="item-center"></td>
                    <td class="item-description">{{ $item['name'] }}<br>{{ $item['description'] }}</td>
                    @foreach($companies as $company)
                        <td class="price">
                            @if(isset($item['companies'][$company->id]) && $item['companies'][$company->id]['price'] > 0)
                                <span class="peso-sign">P</span> {{ number_format($item['companies'][$company->id]['price'], 2) }}
                            @else
                                -
                            @endif
                        </td>
                    @endforeach
                    @if($loop->first)
                        <td rowspan="{{ count($items) + (10 - count($items)) + 1 }}" class="awarded-company">
                            {{ strtoupper($selectedQuotation->company->company_name) }}<br>
                            <span style="font-size: 8pt;">
                                {{ $selectedQuotation->company->address }}
                            </span>
                        </td>
                    @endif
                </tr>
            @endforeach
            
            <!-- Empty rows to match the example -->
            @for($i = 0; $i < 10 - count($items); $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    @foreach($companies as $company)
                        <td>&nbsp;</td>
                    @endforeach
                </tr>
            @endfor
            
            <tr class="total-row">
                <td colspan="4" style="text-align: center">TOTAL=====</td>
                @foreach($companies as $company)
                    <td class="price">
                        <span class="peso-sign">P</span>{{ number_format($companyTotals[$company->id], 2) }}
                    </td>
                @endforeach
            </tr>
        </tbody>
    </table>
    
    <div class="certification">
        We hereby certify that the foregoing is the abstract of price quotations and the award if hereby made to: {{ strtoupper($selectedQuotation->company->company_name) }}.
    </div>
    
    <div class="signatures clearfix">
        <div class="signature-block">
            <p>Prepared by:</p>
            <div class="signature-name">
                {{ $secretary }}
            </div>
            <div class="signature-title">Barangay Secretary</div>
        </div>
        
        <div class="signature-block">
            <p>Approved by:</p>
            <div class="signature-name">
                {{ $treasurer }}
            </div>
            <div class="signature-title">Barangay Treasurer/Requesting Official</div>
        </div>
        
        <div class="signature-block">
            <p>Noted by:</p>
            <div class="signature-name">
                {{ $captain }}
            </div>
            <div class="signature-title">Punong Barangay</div>
        </div>
    </div>
</body>
</html>