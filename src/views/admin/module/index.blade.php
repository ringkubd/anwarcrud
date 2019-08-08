@extends("CRUDGENERATOR::layouts.admin")
@section("content")
    <form action="{{url('final')}}" method="post">

        {{csrf_field()}}

        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="step1-tab" data-toggle="tab" href="#step1" role="tab" aria-controls="home" aria-selected="true">Module Information</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" id="step2-tab" data-toggle="tab" href="#step2" role="tab" aria-controls="profile" aria-selected="false">Table Display</a>
            </li>
            <li class="nav-item">
                <a class="nav-link disabled" id="step3-tab" data-toggle="tab" href="#step3" role="tab" aria-controls="contact" aria-selected="false">Form Display</a>
            </li>
        </ul>
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade in show active step1" id="step1" role="tabpanel" aria-labelledby="home-tab">
                <div class="form-group col-5">
                    <lable for="table">Table</lable>
                    <select name="table" id="table" class="form-control">
                        {!! $data['tableOption'] !!}
                    </select>
                </div>
                <div class="form-group col-5">
                    <lable for="module_name">Module Name</lable>
                    <input class="form-control" type="text" name="module_name" id="module_name">
                </div>
                <div class="form-group col-10">
                    <div class="error"></div>
                    <input type="button" value="Next" class="btn btn-dark">
                </div>
            </div>
            <div class="tab-pane fade in step2" id="step2" role="tabpanel" aria-labelledby="profile-tab">
                <div class="dynamic_content table-responsive">
                    <table class="table table-borderless">
                        <head>
                            <tr>
                                <th>Column</th>
                                <th>Name</th>
                                <th>Join (Optional)</th>
                                <th>Show field name</th>
                            </tr>
                        </head>
                        <tbody id="table-display">

                        </tbody>
                    </table>
                </div>
                <div class="form-group col-5">
                    <input type="button" value="Next" class="btn btn-dark">
                </div>
            </div>

            <div class="tab-pane fade in step3" id="step3" role="tabpanel" aria-labelledby="contact-tab">

                <div class="dynamic_content table-responsive">
                    <table class="table table-borderless">
                        <thead>
                        <tr>
                            <th>Label</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Validation</th>
                            <th>Source Table</th>
                            <th>OnChange</th>
                        </tr>
                        </thead>
                        <tbody id="form-display">

                        </tbody>
                    </table>
                </div>
                <div class="form-group col-5">
                    <input type="submit" value="Create" class="btn btn-dark">
                </div>
            </div>
        </div>
    </form>

@endsection

@section("scripts")
    <script>
        ///Form validation is still a WIP ///

        $(document).ready(function() {
            $('.nav-tabs a').click(function(){
                $(this).tab('show');
                //console.log($(this))
            })

            $("input[type='button']").click(function(event) {
                event.preventDefault();
                var app = $(this);
                var table = $("#table").val();
                var moduleName = $("#module_name").val();
                var regex = /^[a-zA-Z)\(_]{5,}$/g

                if (table == "" || moduleName.length < 5 || !regex.test(moduleName)){
                    $(this).siblings(".error").html("<em class='text-danger'>All field are required and module name length must be more then 5 charector.</em>")
                }else {
                    app.siblings(".error").html("")
                    if (app.parent().parent().hasClass("step1")){
                        getTableDisplay(table,"#table-display");
                        $("#step2-tab").removeClass("disabled");
                        $("#step2-tab").tab("show");
                    }else if (app.parent().parent().hasClass("step2")){
                        app.parent().parent().siblings(".step3").show();
                        console.log(app.parent().parent().siblings(".step3"))
                        getFormDisplay(table,"#form-display");
                        $("#step3-tab").removeClass("disabled");
                        $("#step3-tab").tab("show");
                    }

                }
            })

            String.prototype.replaceAll = function(search, replacement) {
                var target = this;
                return target.split(search).join(replacement);
            };

            var getTableDisplay = function (tablename,selector) {
                $.ajax({
                    url: "{{url('getColumns')}}",
                    method: "post",
                    data: {
                        __token: '{{csrf_token()}}',
                        table: tablename
                    }
                }).done(function (data) {
                    doc =  data.replaceAll("\\n","").replaceAll("\\","");
                    $("#table-display").html("")
                    $("#table-display").html(doc)
                })
            }

            var getFormDisplay = function (tablename,selector) {
                $.ajax({
                    url: "{{url('getFormView')}}",
                    method: "post",
                    data: {
                        __token: '{{csrf_token()}}',
                        table: tablename
                    }
                }).done(function (data) {
                    doc =  data.replaceAll("\\n","").replaceAll("\\","");
                    //doc =  data.replaceAll("n","");
                    $("#form-display").html("")
                    $("#form-display").html(doc)
                })
            }

            $(document).on("change","#table",function () {
                $("#module_name").val($(this).val())
            })



        });

    </script>

@endsection
