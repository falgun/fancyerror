
<html>
    <head>
        <style>
            .activeLine {
                color:white;
                font-weight: bold;
                background-color:cornflowerblue;
                display:block;
            }

            .commentLine {
                color:grey;
            }

            .commentBlock {
                color:grey;
            }

            .spn-green {
                color: green;
                font-weight: bold;
            }

            .hide-seek {
                color: royalblue;
            }

            .text {
                color: olive;
            }

            .text font {
                color: inherit;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row">
                <h2><font color="green">#ERROR#</font></h2>
                <p> Thrown in <b>#FILE#</b> on line <font color="red"><b>#LINE#</b></font></p>
                <p><pre>#CODEBASE#</pre></p>
                <p>Thrown by: #REPORTER#</p>

                <p>Stack Trace : <pre>#STACKTRACE#</pre></p>
                <a target="_blank" href="https://google.com/search?q=#ERROR#" class="btn btn-default"><b>Search on Google</b></a>
                <footer>
                </footer>
            </div>
        </div>
        <!--<script>
            $("span.trace-func").click(function () {
                $(this).children(".hide-seek").toggleClass("hide");
            });
        </script>-->
    </body>
</html>