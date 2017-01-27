<!DOCTYPE html>
<html lang="en">
<!--

For developers, JSON waypoints
/json

-->
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WiiU Title Key</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="/css/bootstrapValidator.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.11/css/dataTables.bootstrap.min.css">
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <link rel="alternate" type="application/rss+xml" title="RSS" href="/rss"/>

    <style>
        .monospace-text {
            font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <h1>Wii U Title Key Database

        <small>
            <a href="/rss" class="btn btn-primary">
                <span class="glyphicon glyphicon-tree-conifer"></span>
                RSS Feed
            </a>
        </small>

        <span class="pull-right">
            <a class="btn btn-lg btn-primary" href="https://github.com/TomEke/WiiU-Title-Key/">Github</a>
        </span>
    </h1>

    <div class="col-sm-8">
        <div class="panel panel-primary">
            <div class="panel-heading">
                Add a title key
            </div>

            <div class="panel-body">

                <div class="col-sm-12">
                    <form id="titleForm" class="form-horizontal" method="post">
                        <div class="form-group-lg">
                            <label class="col-sm-2 control-label" for="titleID">Title ID</label>
                            <div class="col-sm-10">
                                <input name="titleID" type="text" class="form-control" id="titleID" size="20" maxlength="16" placeholder="0004000000000000">
                            </div>
                        </div>
                        <div class="form-group-lg">
                            <label class="col-sm-2 control-label" for="titleKey">Title Key</label>
                            <div class="col-sm-10">
                                <input name="titleKey" type="text" class="form-control" id="titleKey" size="40" maxlength="32" placeholder="ffffffffffffffffffffffffffffffff">
                            </div>
                        </div>
                        <div class="col-sm-offset-2 col-sm-10">
                            <button type="submit" class="btn btn-lg btn-success">Send</button>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>

    <div class="col-sm-2">

        <div class="panel panel-success">
            <div class="panel-heading">
                Upload ticket
            </div>

            <div class="panel-body">

                <form id="keyForm" enctype="multipart/form-data" method="post" action="/uploadticket">
                    <input type="file" name="file" required="required">
                    <button type="submit" class="btn btn-default">Upload</button>
                </form>

            </div>

        </div>

    </div>


    <div class="col-sm-2">

        <div class="panel panel-success">
            <div class="panel-heading">
                Upload keys.txt
            </div>

            <div class="panel-body">

                <form id="keyForm" enctype="multipart/form-data" method="post" action="/uploadkeystxt">
                    <input type="file" name="file" required="required">
                    <button type="submit" class="btn btn-default">Upload</button>
                </form>

            </div>

        </div>

    </div>

    <div class="clearfix"></div>

    <h2>Titles</h2>

    <table class="table table-bordered">
        <thead><tr>
            <th>Title ID</th>
            <th>Title Key</th>
            <th>Name</th>
            <th>Region</th>
            <th>Type</th>
            <th>Ticket</th>
        </tr></thead>
        @foreach (\App\Title::all()->sortByDesc('type') as $title)
            <tr>
                <td class="monospace-text" style="width: 9em;">{{$title->titleID}}</td>
                <td class="monospace-text" style="width: 23em;"><button class="clipboard btn btn-info btn-sm"><span class="glyphicon glyphicon-copy"></span></button> {{$title->titleKey}}</td>
                <td>{{$title->name}}</td>
                <td>{{$title->region}}</td>
                <td>{{$title->type}}</td>
                <td>
                    @if ($title->ticket)
                        <a class="btn btn-success" href="/ticket/{{$title->titleID}}.tik"><span class="glyphicon glyphicon-download-alt"></span></a>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
</div>


<div class="modal fade" id="qrModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Scan this with FBI to install the ticket</h4>
            </div>
            <div class="modal-body" id="qrcode">

            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
<script src="/js/bootstrapValidator.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery.qrcode/1.0/jquery.qrcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.11/js/dataTables.bootstrap.min.js"></script>
<script type="text/javascript">


    function copyToClipboard(text) {
        // create hidden text element, if it doesn't already exist
        var targetId = "_hiddenCopyText_";
        var origSelectionStart, origSelectionEnd;

        // must use a temporary form element for the selection and copy
        target = document.getElementById(targetId);
        if (!target) {
            var target = document.createElement("textarea");
            target.style.position = "fixed";
            target.style.left = "-9999px";
            target.style.top = "0";
            target.id = targetId;
            document.body.appendChild(target);
        }
        target.textContent = text;

        // select the content
        var currentFocus = document.activeElement;
        target.focus();
        target.setSelectionRange(0, target.value.length);

        // copy the selection
        var succeed;
        try {
            succeed = document.execCommand("copy");
        } catch(e) {
            succeed = false;
        }
        // restore original focus
        if (currentFocus && typeof currentFocus.focus === "function") {
            currentFocus.focus();
        }

        target.textContent = "";
        return succeed;
    }

    $(function() {
        var dataTable = $("table").DataTable({
            dom: "<'row'<'filters col-sm-6'l><'col-sm-6'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            paging: false,
            "columnDefs": [
                {
                    "orderable": false, "targets": [1]
                }
            ]
        });
        $(".filters").html(
                '<label>Region <select id="region_select" class="form-control"><option value="">Any</option><option value="ALL">ALL</option><option value="USA">USA</option><option value="EUR">EUR</option><option value="JPN">JPN</option></select></label>' +
                '<label>Type <select id="type_select" class="form-control"><option value="">Any</option><option value="eShop/Application">eShop/Application</option><option value="System Application">System Application</option><option value="Demo">Demo</option><option value="Patch">Patch</option><option value="DLC">DLC</option></label>'
        );
        $("#region_select").on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
            );

            dataTable.column(3).search( val ? '^'+val+'$' : '', true, false ).draw();
        });
        $("#type_select").on('change', function() {
            var val = $.fn.dataTable.util.escapeRegex(
                    $(this).val()
            );

            dataTable.column(4).search( val ? '^'+val+'$' : '', true, false ).draw();
        });
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": true,
            "progressBar": false,
            "positionClass": "toast-top-right",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "5000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
        $(".clipboard").click(function() {
            var row = $(this).parent().siblings();
            if (copyToClipboard(row.eq(0).text() + " " + $.trim($(this).parent().text())) == true) {
                toastr.success("Maybe", "Copied to your clipboard!");
            }
        });

        $('#titleForm')
                .bootstrapValidator({
                    message: 'This value is not valid',
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        titleID: {
                            message: 'The title ID is not valid',
                            validators: {
                                notEmpty: {
                                    message: 'The title ID is required and can\'t be empty'
                                },
                                stringLength: {
                                    min: 16,
                                    max: 16,
                                    message: 'The title ID must be 16 characters long'
                                },
                                regexp: {
                                    regexp: /^[a-fA-F0-9]+$/,
                                    message: 'The title ID must be hexidecimal'
                                }
                            }
                        },
                        titleKey: {
                            validators: {
                                notEmpty: {
                                    message: 'The title key is required and can\'t be empty'
                                },
                                stringLength: {
                                    min: 16,
                                    max: 16,
                                    message: 'The title key must be 16 characters long'
                                },
                                regexp: {
                                    regexp: /^[a-fA-F0-9]+$/,
                                    message: 'The title key must be hexidecimal'
                                }
                            }
                        }
                    }
                })
                .on('success.form.bv', function(e) {
                    // Prevent form submission
                    e.preventDefault();
                    // Get the form instance
                    var $form = $(e.target);
                    // Get the BootstrapValidator instance
                    var bv = $form.data('bootstrapValidator');
                    // Use Ajax to submit form data
                    $.post($form.attr('action'), $form.serialize(), function(result) {
                        if (result == "success") {
                            toastr.success("Thanks for the title key!", "Submission Success");
                        } else if (result == "failure") {
                            toastr.warning("Please double check your title key to make sure it is valid", "Something went wrong");
                        }
                    }, 'text');
                });
    });
</script>
</body>
</html>
