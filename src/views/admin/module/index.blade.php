@extends("CRUDGENERATOR::layouts.admin")
@section("content")
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="fas fa-cogs"></i> Laravel CRUD Generator
                    </h4>
                    <div class="card-actions">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#previewModal">
                            <i class="fas fa-eye"></i> Live Preview
                        </button>
                        <button class="btn btn-secondary" data-toggle="modal" data-target="#stubsModal">
                            <i class="fas fa-file-code"></i> Manage Stubs
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form id="crudGeneratorForm" action="{{url('admin/final')}}" method="post">
                        @csrf

                        <!-- Navigation Tabs -->
                        <ul class="nav nav-tabs nav-justified" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="step1-tab" data-toggle="tab" href="#step1" role="tab">
                                    <i class="fas fa-info-circle"></i> Module Information
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" id="step2-tab" data-toggle="tab" href="#step2" role="tab">
                                    <i class="fas fa-table"></i> Table Display
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link disabled" id="step3-tab" data-toggle="tab" href="#step3" role="tab">
                                    <i class="fas fa-wpforms"></i> Advanced Field Configuration
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <!-- Step 1: Module Information -->
                            <div class="tab-pane fade show active step1" id="step1" role="tabpanel">
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="table">
                                                <i class="fas fa-database"></i> Select Database Table
                                            </label>
                                            <select name="table" id="table" class="form-control" required>
                                                <option value="">-- Select Table --</option>
                                                {!! $data['tableOption'] !!}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="module_name">
                                                <i class="fas fa-cube"></i> Module Name
                                            </label>
                                            <input class="form-control" type="text" name="module_name" id="module_name"
                                                   placeholder="e.g., Post, User, Product" required>
                                            <small class="form-text text-muted">
                                                Must be at least 5 characters. Will be converted to PascalCase.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="api" name="api">
                                                <label class="form-check-label" for="api">
                                                    <i class="fas fa-plug"></i> Generate API Controllers & Resources
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="softdeletes" name="softdeletes">
                                                <label class="form-check-label" for="softdeletes">
                                                    <i class="fas fa-trash-restore"></i> Enable Soft Deletes
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Next Steps:</strong> Select a table to proceed with field configuration.
                                    </div>
                                    <div class="error text-danger"></div>
                                    <button type="button" class="btn btn-primary btn-lg next-step">
                                        <i class="fas fa-arrow-right"></i> Next: Configure Table Display
                                    </button>
                                </div>
                            </div>

                            <!-- Step 2: Table Display -->
                            <div class="tab-pane fade step2" id="step2" role="tabpanel">
                                <div class="mt-4">
                                    <h5><i class="fas fa-table"></i> Table Display Configuration</h5>
                                    <p class="text-muted">Configure which columns to display in the index view and their relationships.</p>

                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th><i class="fas fa-check"></i> Include</th>
                                                    <th><i class="fas fa-columns"></i> Column</th>
                                                    <th><i class="fas fa-tag"></i> Display Name</th>
                                                    <th><i class="fas fa-link"></i> Join (Optional)</th>
                                                    <th><i class="fas fa-eye"></i> Show Field</th>
                                                </tr>
                                            </thead>
                                            <tbody id="table-display">
                                                <!-- Dynamically populated -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="button" class="btn btn-primary btn-lg next-step">
                                            <i class="fas fa-arrow-right"></i> Next: Advanced Field Configuration
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 3: Advanced Field Configuration -->
                            <div class="tab-pane fade step3" id="step3" role="tabpanel">
                                <div class="mt-4">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5><i class="fas fa-cog"></i> Advanced Field Configuration</h5>
                                        <button type="button" class="btn btn-success" id="addField">
                                            <i class="fas fa-plus"></i> Add Custom Field
                                        </button>
                                    </div>
                                    <p class="text-muted">Configure field types, validation rules, and relationships with visual builders.</p>

                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="fieldsConfigTable">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th><i class="fas fa-tag"></i> Label</th>
                                                    <th><i class="fas fa-code"></i> Field Name</th>
                                                    <th><i class="fas fa-list"></i> Type</th>
                                                    <th><i class="fas fa-shield-alt"></i> Validation Rules</th>
                                                    <th><i class="fas fa-link"></i> Relationship</th>
                                                    <th><i class="fas fa-cogs"></i> Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="form-display">
                                                <!-- Dynamically populated -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-rocket"></i> Generate CRUD Module
                                        </button>
                                        <button type="button" class="btn btn-info btn-lg ml-2" id="previewBtn">
                                            <i class="fas fa-eye"></i> Preview Generated Files
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Generated Modules List -->
            <div class="card mt-4">
                <div class="card-header">
                    <h5><i class="fas fa-list"></i> Generated Modules</h5>
                </div>
                <div class="card-body">
                    @if(isset($modules) && $modules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Module</th>
                                        <th>Table</th>
                                        <th>API</th>
                                        <th>Soft Deletes</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($modules as $module)
                                        <tr>
                                            <td><strong>{{ $module->name }}</strong></td>
                                            <td><code>{{ $module->table_name ?? $module->uri }}</code></td>
                                            <td>
                                                @if($module->api ?? false)
                                                    <span class="badge badge-success">Yes</span>
                                                @else
                                                    <span class="badge badge-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($module->softdeletes ?? false)
                                                    <span class="badge badge-info">Yes</span>
                                                @else
                                                    <span class="badge badge-secondary">No</span>
                                                @endif
                                            </td>
                                            <td>{{ $module->created_at ?? 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('admin.crudgenerator.docs.view', $module->name) }}"
                                                   class="btn btn-info btn-sm" target="_blank">
                                                    <i class="fas fa-book"></i> Docs
                                                </a>
                                                <form method="POST" action="{{ route('admin.crudgenerator.delete', $module->name) }}"
                                                      style="display: inline-block;"
                                                      onsubmit="return confirm('Are you sure? This will delete all generated files.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No modules generated yet. Create your first CRUD module above!
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye"></i> Live Preview - Generated Files
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Loading preview...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stubs Management Modal -->
<div class="modal fade" id="stubsModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-file-code"></i> Manage Custom Stubs
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Upload Form -->
                <form action="{{ route('admin.crudgenerator.stubs.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Upload New Stub</label>
                        <input type="file" name="stub_file" class="form-control-file" accept=".stub,.txt,.php">
                    </div>
                    <div class="form-group">
                        <label>Stub Name</label>
                        <input type="text" name="stub_name" class="form-control" placeholder="e.g., custom-model.stub">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-upload"></i> Upload Stub
                    </button>
                </form>

                <hr>

                <!-- Existing Stubs -->
                <h6>Existing Custom Stubs</h6>
                <div id="stubsList">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin"></i> Loading stubs...
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section("scripts")
<script>
$(document).ready(function() {
    // Laravel validation rules options
    const validationRules = [
        'required', 'nullable', 'string', 'integer', 'numeric', 'boolean', 'date', 'email', 'url', 'file', 'image',
        'min', 'max', 'between', 'in', 'not_in', 'regex', 'unique', 'exists', 'confirmed', 'array', 'json',
        'alpha', 'alpha_dash', 'alpha_num', 'digits', 'digits_between', 'mimes', 'mimetypes', 'ip', 'ipv4', 'ipv6'
    ];

    // Tab navigation
    $('.nav-tabs a').click(function(){
        $(this).tab('show');
    });

    // String prototype for replaceAll
    String.prototype.replaceAll = function(search, replacement) {
        return this.split(search).join(replacement);
    };

    // Auto-fill module name from table selection
    $(document).on("change", "#table", function () {
        const tableName = $(this).val();
        if (tableName) {
            const moduleName = tableName.split('_').map(word =>
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join('');
            $("#module_name").val(moduleName);
        }
    });

    // Next step buttons
    $(".next-step").click(function(event) {
        event.preventDefault();
        const app = $(this);
        const table = $("#table").val();
        const moduleName = $("#module_name").val();
        const regex = /^[a-zA-Z)(_]{5,}$/g;

        if (table === "" || moduleName.length < 5 || !regex.test(moduleName)) {
            app.siblings(".error").html("<em class='text-danger'>All fields are required and module name must be at least 5 characters.</em>");
            return;
        }

        app.siblings(".error").html("");

        if (app.closest(".step1").length) {
            getTableDisplay(table, "#table-display");
            $("#step2-tab").removeClass("disabled").tab("show");
        } else if (app.closest(".step2").length) {
            getFormDisplay(table, "#form-display");
            $("#step3-tab").removeClass("disabled").tab("show");
        }
    });

    // Get table display configuration
    function getTableDisplay(tablename, selector) {
        $.ajax({
            url: "{{url('admin/getColumns')}}",
            method: "post",
            data: {
                _token: '{{csrf_token()}}',
                table: tablename
            }
        }).done(function (data) {
            const doc = JSON.stringify(data).replaceAll("\\n","").replaceAll("\\","");
            $(selector).html(JSON.parse(doc));
        }).fail(function() {
            $(selector).html('<tr><td colspan="5" class="text-center text-danger">Failed to load table columns</td></tr>');
        });
    }

    // Get form display configuration
    function getFormDisplay(tablename, selector) {
        $.ajax({
            url: "{{url('admin/getFormView')}}",
            method: "post",
            data: {
                _token: '{{csrf_token()}}',
                table: tablename
            }
        }).done(function (data) {
            const doc = JSON.stringify(data).replaceAll("\\n","").replaceAll("\\","");
            $(selector).html(JSON.parse(doc));
            initializeAdvancedFieldControls();
        }).fail(function() {
            $(selector).html('<tr><td colspan="6" class="text-center text-danger">Failed to load form fields</td></tr>');
        });
    }

    // Initialize advanced field controls
    function initializeAdvancedFieldControls() {
        // Add configure buttons to each field row
        $('#form-display tr').each(function() {
            const $row = $(this);
            const $lastCell = $row.find('td:last');

            if (!$lastCell.find('.configure-field').length) {
                $lastCell.append(`
                    <button type="button" class="btn btn-sm btn-info configure-field" title="Configure Field">
                        <i class="fas fa-cogs"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger remove-field ml-1" title="Remove Field">
                        <i class="fas fa-trash"></i>
                    </button>
                `);
            }
        });
    }

    // Live Preview
    $('#previewBtn').click(function() {
        const formData = $('#crudGeneratorForm').serialize();

        $.ajax({
            url: "{{ route('admin.crudgenerator.preview') }}",
            method: "POST",
            data: formData + '&_token=' + $('meta[name="csrf-token"]').attr('content'),
            beforeSend: function() {
                $('#previewContent').html(`
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p>Generating preview...</p>
                    </div>
                `);
                $('#previewModal').modal('show');
            }
        }).done(function(response) {
            let previewHtml = '<div class="accordion" id="previewAccordion">';

            Object.keys(response.files).forEach((fileType, index) => {
                const file = response.files[fileType];
                previewHtml += `
                    <div class="card">
                        <div class="card-header" id="heading${index}">
                            <h5 class="mb-0">
                                <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapse${index}">
                                    <i class="fas fa-file-code"></i> ${fileType}: ${file.path}
                                </button>
                            </h5>
                        </div>
                        <div id="collapse${index}" class="collapse ${index === 0 ? 'show' : ''}" data-parent="#previewAccordion">
                            <div class="card-body">
                                <pre><code class="language-php">${file.code}</code></pre>
                            </div>
                        </div>
                    </div>
                `;
            });

            previewHtml += '</div>';
            $('#previewContent').html(previewHtml);
        }).fail(function() {
            $('#previewContent').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> Failed to generate preview.
                </div>
            `);
        });
    });

    // Load stubs when modal opens
    $('#stubsModal').on('show.bs.modal', function() {
        loadStubsList();
    });

    function loadStubsList() {
        $.ajax({
            url: "{{ route('admin.crudgenerator.stubs.list') }}",
            method: "GET"
        }).done(function(response) {
            let stubsHtml = '<div class="list-group">';

            if (response.stubs.length === 0) {
                stubsHtml += '<div class="list-group-item">No custom stubs found.</div>';
            } else {
                response.stubs.forEach(stub => {
                    stubsHtml += `
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${stub.name}</strong>
                                <br>
                                <small class="text-muted">
                                    Size: ${(stub.size / 1024).toFixed(1)} KB |
                                    Modified: ${stub.modified}
                                </small>
                            </div>
                            <button class="btn btn-sm btn-danger" onclick="deleteStub('${stub.name}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    `;
                });
            }

            stubsHtml += '</div>';
            $('#stubsList').html(stubsHtml);
        }).fail(function() {
            $('#stubsList').html('<div class="alert alert-danger">Failed to load stubs.</div>');
        });
    }

    // Global function for deleting stubs
    window.deleteStub = function(stubName) {
        if (confirm(`Delete stub "${stubName}"?`)) {
            // Implementation for stub deletion would go here
            alert('Stub deletion feature to be implemented');
        }
    };

    // Form submission enhancement
    $('#crudGeneratorForm').submit(function(e) {
        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
    });
});
</script>

<!-- Syntax Highlighting -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
@endsection
