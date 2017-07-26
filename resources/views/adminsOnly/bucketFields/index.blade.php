@extends('layouts.dataTable')

@section('page_title', 'Buckets')

@section('content')
    <!-- Page Content -->
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Manage Fields for Bucket {{ $selectedOptions[$fieldType]}}</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li class="active">All Bucket Fields</li>
                </ol>

            </div>

        </div>
        <!-- /row -->
        <div class="row">
            <div class="col-sm-12">
                <div class="white-box">
                    Please select field Type:
                    <select name="manageFieldsFor" id="manageFieldsFor">
                        @foreach($selectedOptions as $optionKey => $optionValue)
                            <?php $selected = ($optionKey==$fieldType) ? 'selected' : '';?>
                            <option value="/{{$optionKey}}" <?php echo $selected; ?>> {{$optionValue}}</option>
                        @endforeach
                    </select>
                    <a href="{{ url('/add-field').DIRECTORY_SEPARATOR.$fieldType }}">
                        <button type="button" class="btn btn-info waves-effect waves-light m-r-10" id="addFiles">Add {{ $selectedOptions[$fieldType]}} Option</button>
                    <a>
                    <div class="table-responsive">
                        <table id="example23" class="table table-striped table_grid">
                            <thead>
                            <tr>
                                {{--<th></th>--}}
                                <th></th>
                                <th>Name</th>
                                <th>Value</th>
                                <?php if($fieldType=='region') echo '<th>Code</th>';?>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                                $counter = 1;
                                $fieldName = $fieldType.'_name';
                                $fieldValue = $fieldType.'_value';
                                $fieldCode = $fieldType.'_code';
                            ?>
                            @foreach($fieldsArray as $fieldData)
                                <tr>
                                    {{--<td> <input type="checkbox" name="fieldNames[]" value=""></td>--}}
                                    <td>{{ $counter}}</td>
                                    <td>{{ $fieldData->$fieldName }}</td>
                                    <td>{{ $fieldData->$fieldValue }}</td>
                                    <?php if($fieldType=='region') echo '<td>'.(!empty($fieldData->$fieldCode) ? $fieldData->$fieldCode : '-').'</td>';?>
                                    <td class="record_actions">
                                        <a href="{{ url('/edit-field/'.$fieldType.DIRECTORY_SEPARATOR.$fieldData->id) }}" title="edit" class="btn btn-primary-btn"><i class="fa fa-edit"></i></a>
                                        <a href="{{ url('/delete-field/'.$fieldType.DIRECTORY_SEPARATOR.$fieldData->id) }}" title="delete" class="btn btn-danger-btn"
                                           onclick="return confirm('Are you sure you want to delete {{ $fieldData->$fieldName }}?')">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php $counter++; ?>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->
    <script>
        $(document).ready(function(){
            $(document).on('change','#manageFieldsFor',function(){
                var selectedField = $(this).val();
                var redirectFieldPage = '{{ url('/manage-bucket-fields/') }}'+selectedField;
                window.location.href = redirectFieldPage;
            });
        });
    </script>
@endsection