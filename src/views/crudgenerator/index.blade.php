@extends('layouts.admin')

@section('title', 'CRUD Generator')

@section('content')
    <h1>Admin CRUD Generator</h1>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if(session('output'))
        <div class="alert alert-info" style="white-space: pre-line;">
            <strong>Command Output:</strong><br>
            {{ session('output') }}
        </div>
    @endif
    <div class="card mb-4">
        <div class="card-header">Run CRUD Generator Command</div>
        <div class="card-body">
            <form id="crud-generator-form" method="POST" action="{{ route('admin.crudgenerator.run') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label for="module">Module Name</label>
                        <input type="text" class="form-control" id="module" name="module" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="fields">Fields (e.g. title:string,body:text)</label>
                        <input type="text" class="form-control" id="fields" name="fields" required>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="relationships">Relationships</label>
                        <input type="text" class="form-control" id="relationships" name="relationships" placeholder="user:belongsTo">
                    </div>
                    <div class="form-group col-md-1 d-flex align-items-end">
                        <div class="form-check mr-2">
                            <input class="form-check-input" type="checkbox" id="api" name="api">
                            <label class="form-check-label" for="api">API</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="softdeletes" name="softdeletes">
                            <label class="form-check-label" for="softdeletes">Soft Deletes</label>
                        </div>
                    </div>
                    <div class="form-group col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-block mr-2">Run Generator</button>
                        <button type="button" class="btn btn-secondary btn-block" id="preview-btn">Preview</button>
                    </div>
                </div>
            </form>
            <!-- Preview Modal -->
            <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="previewModalLabel">Live Preview: Files to be Generated</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="preview-modal-body">
                            <div class="text-center text-muted">Loading preview...</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('preview-btn').addEventListener('click', function() {
                    var form = document.getElementById('crud-generator-form');
                    var formData = new FormData(form);
                    fetch("{{ route('admin.crudgenerator.preview') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        var html = '<div class="list-group">';
                        for (const [key, file] of Object.entries(data.files)) {
                            html += `<div class="list-group-item">
                                <strong>${file.path}</strong>
                                <pre style="background:#f8f9fa; border:1px solid #eee; padding:10px; margin-top:5px;">${file.code.replace(/</g, '&lt;')}</pre>
                            </div>`;
                        }
                        html += '</div>';
                        document.getElementById('preview-modal-body').innerHTML = html;
                        $('#previewModal').modal('show');
                    })
                    .catch(() => {
                        document.getElementById('preview-modal-body').innerHTML = '<div class="alert alert-danger">Failed to load preview.</div>';
                        $('#previewModal').modal('show');
                    });
                });
            });
            </script>
        </div>
    </div>
    <h2>Generated Modules</h2>
    <hr>
    <h2>Custom Stub Management</h2>
    <div class="alert alert-info">
        <strong>Instructions:</strong><br>
        <ul>
            <li>Stubs are template files used for code generation (e.g., model, controller, migration, views).</li>
            <li>To override a stub, upload or edit a file in <code>resources/crud-stubs/</code> with the same name as the default stub (e.g., <code>model.stub</code>, <code>controller.stub</code>).</li>
            <li>If a custom stub exists, it will be used instead of the package default.</li>
            <li>You can edit stubs directly here or upload new ones.</li>
        </ul>
        <strong>Sample stub names:</strong> <code>model.stub</code>, <code>controller.stub</code>, <code>migration.stub</code>, <code>view.stub</code>
    </div>
    <div class="card mb-4">
        <div class="card-header">Manage Stubs</div>
        <div class="card-body">
            <form id="stub-upload-form" method="POST" action="{{ route('admin.crudgenerator.stub.upload') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-row align-items-end">
                    <div class="form-group col-md-4">
                        <label for="stub_file">Upload New Stub</label>
                        <input type="file" class="form-control-file" id="stub_file" name="stub_file" required>
                    </div>
                    <div class="form-group col-md-4">
                        <label for="stub_name">Stub Name (e.g. model.stub)</label>
                        <input type="text" class="form-control" id="stub_name" name="stub_name" required>
                    </div>
                    <div class="form-group col-md-2">
                        <button type="submit" class="btn btn-success">Upload Stub</button>
                    </div>
                </div>
            </form>
            <hr>
            <h5>Available Stubs in <code>resources/crud-stubs/</code>:</h5>
            <ul id="stub-list">
                <!-- Will be loaded by JS -->
            </ul>
        </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Load stub list via AJAX
        fetch("{{ route('admin.crudgenerator.stub.list') }}")
            .then(response => response.json())
            .then(data => {
                var html = '';
                if (data.stubs && data.stubs.length) {
                    data.stubs.forEach(function(stub) {
                        html += `<li><strong>${stub}</strong> <a href="#" class="edit-stub-link" data-stub="${stub}">Edit</a></li>`;
                    });
                } else {
                    html = '<li class="text-muted">No custom stubs found.</li>';
                }
                document.getElementById('stub-list').innerHTML = html;
            });
        // TODO: Add JS for editing stubs inline/modal
    });
    </script>
    <table class="table">
        <thead>
            <tr>
                <th>Module Name</th>
                <th>Controller</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($modules as $module)
                <tr>
                    <td>{{ $module->name }}</td>
                    <td>{{ $module->controllers }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.crudgenerator.delete', $module->name) }}" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this module and all generated files?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
