<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PURCHASE REQUEST</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 20px;
      color: #000;
    }
    .center {
      text-align: center;
    }
    .bordered {
      border: 1px solid #000;
      border-collapse: collapse;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 10px;
    }
    th, td {
      border: 1px solid #000;
      padding: 8px 8px;
      font-size: 14px;
      text-align: left;
    }
    th {
      background: #f5f5f5;
    }
    .no-border {
      border: none !important;
    }
    .signature {
      height: 35px;  
      border-bottom: 1px solid #000;
      margin-bottom: 1px;
      margin-left: 40px; 
      margin-right: 40px; 
    }
    .signature-date {
      height: 25px;  
      border-bottom: 1px solid #000;
      margin-top: 8px;
      margin-bottom: 1px;
      margin-left: 48px;  
      margin-right: 48px;  
    }
    .bold {
      font-weight: bold;
    }
    .total-row {
      font-weight: bold;
      background-color: #f5f5f5;
    }
    .text-center {
      text-align: center;
    }
    .right {
      text-align: right;
    }
    .w-50 {
      width: 48%;
    }
    .mt-2 {
      margin-top: 12px;
    }
    .mb-2 {
      margin-bottom: 12px;
    }
    .text-xs {
      font-size: 12px;
    }
    .text-muted-foreground {
      color: #888;
    }
  </style>
</head>
<body>
  <table>
    <tr>
      <td colspan="6" class="text-center"><h3>PURCHASE REQUEST</h3></td>
    </tr>
    <tr>
      <td colspan="2">Barangay:<br><span class="bold">BLUE RIDGE B</span></td>
      <td colspan="2">P.R. No.:<br><span class="bold">{{ $prNumber }}</span></td>
      <td colspan="2">Date:<br><span class="bold">{{ $date ?? '' }}</span></td>
    </tr>
    <tr>
      <td colspan="3">City/Municipality:<br><span class="bold">QUEZON CITY</span></td>
      <td colspan="3">Province:<br><span class="bold">METRO MANILA</span></td>
    </tr>
    <tr>
      <th colspan="6" class="text-center">SELECTED SUPPLIER</th>
    </tr>
    <tr>
      <td colspan="2">Company Name:<br><span class="bold">{{ $selectedQuotation->company->company_name }}</span></td>
      <td colspan="2">Contact Person:<br><span class="bold">{{ $selectedQuotation->company->contact_person }}</span></td>
      <td colspan="2">Contact Number:<br><span class="bold">{{ $selectedQuotation->company->contact_number }}</span></td>
    </tr>
    <tr>
      <td colspan="6">Address:<br><span class="bold">{{ $selectedQuotation->company->address }}</span></td>
    </tr>
    <tr>
      <th colspan="6" class="text-center">REQUISITION</th>
    </tr>
    <tr>
      <th>Item No.</th>
      <th>Qty</th>
      <th>Item Name</th>
      <th>Item Description</th>
      <th>Unit Cost</th>
      <th>Total Amount</th>
    </tr>
    @foreach($selectedQuotation->items as $index => $item)
    <tr>
      <td class="text-center">{{ $index + 1 }}</td>
      <td class="text-center">{{ $item->quantity }}</td>
      <td class="text-center"> {{ $item->item_name }}</td>
      <td class="text-center">{{ $item->description }}</td>
      <td class="right">PHP {{ number_format($item->price, 2) }}</td>
      <td class="right">PHP {{ number_format($item->price * $item->quantity, 2) }}</td>
    </tr>
    @endforeach
    <tr class="total-row">
      <td colspan="5" class="right">TOTAL</td>
      <td class="right">PHP {{ number_format($selectedQuotation->items->sum(function($item) {
        return $item->price * $item->quantity;
      }), 2) }}</td>
    </tr>
    <tr>
      <td colspan="6"><span class="bold">Purpose:</span> {{ $request->description }}</td>
    </tr> 
  </table>
  <table>
    <tr>
      <td colspan="3" class="text-center" style="vertical-align:bottom;">
          <div class="signature"></div>
          <span class="bold">{{ $treasurer->name ?? 'No Treasurer Assigned' }}</span><br>
          <span class="text-xs">Signature over Printed Name</span><br>
          <span class="text-xs">Barangay Treasurer</span>
          <div class="signature-date"></div>
          <span class="text-xs">Date</span>
      </td>
      <td colspan="3" class="text-center" style="vertical-align:bottom;">
          <div class="signature"></div>
          <span class="bold">{{ $captain->name ?? 'No Captain Assigned' }}</span><br>
          <span class="text-xs">Signature over Printed Name</span><br>
          <span class="text-xs">Punong Barangay</span>
          <div class="signature-date"></div>
          <span class="text-xs">Date</span>
      </td>
    </tr>
  </table>
</body>
</html>