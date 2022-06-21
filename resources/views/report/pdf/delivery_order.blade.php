<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Order</title>

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
            <p><b style="font-size: large;">Delivery Order (Surat Jalan)</b></p>
        </div>
        <div class="column">
            <div style="text-align: right">
                <img src="{{asset('images/rsch.png')}}" style="width:100px; height:110px"></img>
                <p>Printed by: Eka Pratama {{date("d M Y h:i:s")}}</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="column">
            <table>
                <tr>
                    <td>
                        <strong>Code Delivery Order</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        RS/0902334/039543
                    </td>
                </tr>
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
                        <strong>Delivery Date</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Plant 1
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Request by</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Plant 1
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Approved by</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Plant 1
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Duration</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Plant 1
                    </td>
                </tr>
            </table>
        </div>
        <div class="column" style="text-align: right">
            <table>
                <tr>
                    <td>
                        <strong>Plant</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        RS/0902334/039543
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Receiver</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        RS/0902334/039543
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Responsible Person</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        RS/0902334/039543
                    </td>
                </tr>
                <tr>
                    <td>
                        <strong>Note</strong>
                    </td>
                    <td width="10">:</td>
                    <td>
                        Plant 1
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <br> <br>
    <table width="100%" class="table-center">
        <thead>
            <tr class="border_bottom">
                <td style="widtd: 30px">No</td>
                <td>Material</td>
                <td>Request Quantity</td>
                <td>Delivery Quantity</td>
                <td>UoM</td>
                <td>Supply Form</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>MT 001</td>
                <td>3</td>
                <td>3</td>
                <td>Unit</td>
                <td>Vendor 1</td>
                <td>
                    <tr style="margin-bottom: 0px; padding-bottom: 0px">
                        <td colspan="9" align="justify" style="margin-bottom: 0px; padding-bottom: 0px; border:none">
                            <strong>Serial Numbers: </strong>
                            <span style="margin-top:5px; margin-bottom: 0px; padding-bottom: 0px; text-align: justify;">
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                                11, 22, 33
                            </span>
                        </td>
                    </tr>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>