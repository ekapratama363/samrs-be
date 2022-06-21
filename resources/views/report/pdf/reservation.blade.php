<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        tr.border_bottom td {
            border-bottom: 1px solid black;
        }

        .table-center {
            text-align: center;
            vertical-align: middle;
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
            <p><b style="font-size: large;">Reservation</b></p>

            <table>
                <tr>
                    <td>
                        <strong>Code Reservation</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        RS/0902334/039543
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Plant</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Plant 1
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Receive</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Room 1
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Request By</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        dadang
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Reservation Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        2020-01-01 10:01:01
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Update By</strong>
                    </td>
                    <td width="10">:</td>
                    <td>Eka</td>
                </tr>
                <tr>
                    <td>
                        <strong>Update Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        2020-01-01 10:01:01
                    </td>
                </tr>
            </table>
        </div>
        <div class="column">
            <div style="text-align: right">
                <img src="{{asset('images/rsch.png')}}" style="width:100px; height:110px"></img>
                <p>Printed by: Eka Pratama </p>
                <p>{{date("d M Y h:i:s")}}</p>
            </div>
        </div>
    </div>

    <br> <br>
    <table width="100%" class="table-center">
        <thead>
            <tr class="border_bottom">
                <td style="widtd: 30px">No</td>
                <td>Material</td>
                <td>Quantity</td>
                <td>UoM</td>
                <td>Supply Form</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>MT 001</td>
                <td>3</td>
                <td>Unit</td>
                <td>Vendor 1</td>
            </tr>
        </tbody>
    </table>
    <html-separator/>
</body>

</html>