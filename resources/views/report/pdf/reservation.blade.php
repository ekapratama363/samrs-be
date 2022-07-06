<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation</title>

    <style>
        tr.border_bottom td {
            border: 0.1px solid;
        }

        .table-center {
            text-align: center;
            vertical-align: middle;
            border: 0.1px solid;
            border-collapse: collapse;
        }

        .container:after {
            content: "";
            display: table;
            clear: both;
        }

        .column {
            float: left;
            width: 50%;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="column">
            <p>
                <b style="font-size: large;">
                @if ($reservation->approved) 
                    Purchase Order
                @else 
                    Reservation
                @endif
                </b>
            </p>

            <table>
                <tr>
                    <td>
                        <strong>Code Reservation</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->code }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Plant</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->plant ? $reservation->plant->name : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Receive</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->room_receiver ? $reservation->room_receiver->name : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Request By</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->createdBy ? $reservation->createdBy->fullname : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Reservation Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->created_at }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Approved By</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->approved ? $reservation->approved->fullname : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Approved Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        {{ $reservation->approved ? $reservation->approved_or_rejected_at : '-' }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Sender</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        @if ($reservation->vendor) 
                            {{ $reservation->vendor->name }}
                        @elseif ($reservation->room_sender)
                            {{ $reservation->room_sender->name }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <div class="column">
            <div style="text-align: right">
                <img src="{{asset('images/rsch.png')}}" style="width:100px; height:110px"></img>
                <!-- <p>Printed by: Eka Pratama </p> -->
                <p>Printed at: {{ date("d M Y H:i:s") }}</p>
            </div>
        </div>
    </div>

    <br> <br>
    <table width="100%" class="table-center">
        <thead>
            <tr class="border_bottom">
                <td style="widtd: 30px">No</td>
                <td>Material</td>
                @if ($reservation->approved) <td>Price (Rp)</td> @endif
                <td>Quantity</td>
                @if ($reservation->approved) <td>Subtotal (Rp)</td> @endif
                <td>UoM</td>
            </tr>
        </thead>
        <tbody>
            @foreach($reservation->details as $detail)
            <tr class="border_bottom">
                <td>{{ $loop->iteration }}</td>
                <td style="text-align: left">{{ $detail->material->material_code }}</td>
                
                @if ($reservation->approved) 
                <td style="text-align: right">
                    {{ number_format($detail->price) }} 
                </td> 
                @endif

                <td>
                    {{ $detail->quantity }}

                    @if ($detail->material->quantity_uom > 1)
                        ({{ $detail->quantity * $detail->material->quantity_uom }})
                    @endif
                </td>

                @if ($reservation->approved) 
                <td style="text-align: right">
                    {{ number_format($detail->subtotal) }} 
                </td>
                @endif

                <td>{{ $detail->material->uom ? $detail->material->uom->name : '-' }}</td>
            </tr>
            @endforeach
            <tr class="border_bottom">
                <td colspan="{{ $reservation->approved ? 6 : 4 }}" style="text-align: right">
                    Total Price: <b>Rp. {{ number_format($reservation->total_price) }}</b>
                </td>
            </tr>
        </tbody>
    </table>
    <html-separator/>
</body>

</html>