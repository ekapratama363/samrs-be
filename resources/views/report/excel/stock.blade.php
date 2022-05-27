<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stocks</title>
</head>
<body>
    <table>
        <tr>
            <th>No</th>
            <th>Plant</th>
            <th>Room</th>
            <th>Material</th>
            <th>Description</th>
            <th>Uom</th>
            <th>Ready Stock</th>
            <th>Quantity in Transit</th>
            <th>Minimum Stock</th>
            <th>Require Serial Number?</th>
            <th>Serial Numbers</th>
        </tr>
        @foreach($stocks as $stock)
        <tr>
            <td>{{$loop->iteration}}</td>
            <td>{{ $stock->room->plant ? $stock->room->plant->name : '' }}</td>
            <td>{{ $stock->room ? $stock->room->name : '' }}</td>
            <td>{{ $stock->material ? $stock->material->material_code : '' }}</td>
            <td>{{ $stock->material ? $stock->material->description : '' }}</td>
            <td>{{ $stock->material->uom ? $stock->material->uom->name : '' }}</td>
            <td>{{ $stock->stock }}</td>
            <td>{{ $stock->quantity_in_transit }}</td>
            <td>{{ $stock->minimum_stock }}</td>
            <td>
                {{ 
                    $stock->material 
                    ? 
                    $stock->material->serial_number ? 'Yes' : 'No' 
                    : '' 
                }}
            </td>
            <td>
                @if (count($stock->stock_histories) > 0) 
                    @foreach($stock->stock_histories as $history)
                        @if ($history->status == 1 || $history->status == 3)
                            <p>{{$history->serial_number}}</p>,
                        @endif
                    @endforeach
                @endif 
            </td>
        </tr>
        @endforeach
    </table>
</body>
</html>