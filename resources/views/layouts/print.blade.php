<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include(backpack_view('inc.head'))

</head>

<body>
    <div id="app">

        <div style="position: fixed; top:1rem; right:1rem;" class="text-center  noPrint">
            <div class="text-center btn btn-primary noPrint" onclick="printPage()">
                <i class="la la-print"></i> طباعة
            </div>
        </div>

        @yield('content')


        <style>
            @media print {
                .noPrint {
                    display: none;
                }
            }

            @media print {
                .new-page {
                    page-break-after: always;
                }
            }

            body {
                background-color: #fff !important;
            }

            .app {
                padding: 20px;
            }

            table.table {
                border-collapse: collapse;
                margin: 1%;
                width: 98%;
            }

            table.table td,
            table.table th {
                border: 1px solid black !important;
            }

            table.table.table-no-border td,
            table.table.table-no-border th {
                border: 0 solid black !important;
            }
        </style>

        <script>
            // window.print();

            function printPage() {
                window.print();
                console.log('printpage');

            }
        </script>

        @yield('script')
    </div>
</body>

</html>
