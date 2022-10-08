@extends('layouts.admin')

@section('content')
    <input type="hidden" id="headerdata" value="{{ __('PRODUCT') }}">
    <div class="content-area">
        <div class="mr-breadcrumb">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading">{{ __('Products') }}</h4>
                    <ul class="links">
                        <li>
                            <a href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }} </a>
                        </li>
                        <li>
                            <a href="javascript:;">{{ __('Products') }} </a>
                        </li>
                        <li>
                            <a href="{{ route('admin-prod-index') }}">{{ __('All Products') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>



        <div class="product-area">
            <div class="row">
                <div class="col-lg-12">
                    <div class="mr-table">
                        <form class="form-inline form-validate" id="filter_form"
                            action="{{ route('admin-prod-datatables') }}" enctype="multipart/form-data">

                            <div class="form-group">
                                <h6 style="font-weight: bold; padding:0px 5px; ">Filter By Category:</h6>
                            </div>

                            <div class="input-group mr-2">
                                <select class="form-control" name="category_id" style="min-width: 200px">
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="input-group mr-2">
                                <input type="text" name="keywords" class="form-control" placeholder="Products">
                            </div>

                            <button type="button" id="btn-search" class="btn btn-info primary-bg mr-2">Search</button>
                            <a href="#" id="btn-clear" class="btn btn-outline-info">Clear Search</a>
                            {{--											<button type="button" class="btn btn-info primary-bg updateAll ml-auto" style="">Update Products</button> --}}
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 d-flex justify-content-end">
                    <div class="p-1">
                        <button type="button" id="bulk_active_btn" class="btn btn-success primary-bg updateAll ml-auto"
                            style="">Activated</button>
                        <button type="button" id="bulk_de_active_btn" class="btn btn-danger primary-bg updateAll ml-auto"
                            style="">Deactivated</button>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="mr-table allproduct">

                        @include('includes.admin.form-success')

                        <div class="table-responsiv">
                            <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Image') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th>{{ __('Stock') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th>{{ __('Select') }}</th>
                                        <th>{{ __('Options') }}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>




    </div>



    {{-- HIGHLIGHT MODAL --}}

    <div class="modal fade" id="modal2" tabindex="-1" role="dialog" aria-labelledby="modal2" aria-hidden="true">


        <div class="modal-dialog highlight" role="document">
            <div class="modal-content">
                <div class="submit-loader">
                    <img src="{{ asset('assets/images/' . $gs->admin_loader) }}" alt="">
                </div>
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- HIGHLIGHT ENDS --}}

    {{-- CATALOG MODAL --}}

    <div class="modal fade" id="catalog-modal" tabindex="-1" role="dialog" aria-labelledby="modal1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header d-block text-center">
                    <h4 class="modal-title d-inline-block">{{ __('Update Status') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>


                <!-- Modal body -->
                <div class="modal-body">
                    <p class="text-center">{{ __('You are about to change the status of this Product.') }}</p>
                    <p class="text-center">{{ __('Do you want to proceed?') }}</p>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <a class="btn btn-success btn-ok">{{ __('Proceed') }}</a>
                </div>

            </div>
        </div>
    </div>

    {{-- CATALOG MODAL ENDS --}}


    {{-- DELETE MODAL --}}

    <div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="modal1"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header d-block text-center">
                    <h4 class="modal-title d-inline-block">{{ __('Confirm Delete') }}</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <!-- Modal body -->
                <div class="modal-body">
                    <p class="text-center">{{ __('You are about to delete this Product.') }}</p>
                    <p class="text-center">{{ __('Do you want to proceed?') }}</p>
                </div>

                <!-- Modal footer -->
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('Cancel') }}</button>
                    <a class="btn btn-danger btn-ok">{{ __('Delete') }}</a>
                </div>

            </div>
        </div>
    </div>

    {{-- DELETE MODAL ENDS --}}


    {{-- GALLERY MODAL --}}

    <div class="modal fade" id="setgallery" tabindex="-1" role="dialog" aria-labelledby="setgallery"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{ __('Image Gallery') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="top-area">
                        <div class="row">
                            <div class="col-sm-6 text-right">
                                <div class="upload-img-btn">
                                    <form method="POST" enctype="multipart/form-data" id="form-gallery">
                                        {{ csrf_field() }}
                                        <input type="hidden" id="pid" name="product_id" value="">
                                        <input type="file" name="gallery[]" class="hidden" id="uploadgallery"
                                            accept="image/*" multiple>
                                        <label for="image-upload" id="prod_gallery"><i
                                                class="icofont-upload-alt"></i>{{ __('Upload File') }}</label>
                                    </form>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <a href="javascript:;" class="upload-done" data-dismiss="modal"> <i
                                        class="fas fa-check"></i> {{ __('Done') }}</a>
                            </div>
                            <div class="col-sm-12 text-center">( <small>{{ __('You can upload multiple Images') }}
                                    .</small> )
                            </div>
                        </div>
                    </div>
                    <div class="gallery-images">
                        <div class="selected-image">
                            <div class="row">


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- GALLERY MODAL ENDS --}}
@endsection



@section('scripts')
    {{-- DATA TABLE --}}

    <script type="text/javascript">
        $('#btn-search').on('click', function() {
            $("#geniustable").dataTable().fnDestroy();
            $('#geniustable tbody').html('');
            var category_id = $("select[name='category_id']").val();
            var keywords = $("input[name='keywords']").val();
            // $("#geniustable").dataTable().fnDestroy();
            products(category_id, keywords);
        })

        $('#btn-clear').on('click', function() {
            $("#geniustable").dataTable().fnDestroy();
            $('#geniustable tbody').html('');
            $('#filter_form').trigger('reset');

            products();
        })

        function products(cat = null, keyw = null) {
            $('#geniustable').DataTable({
                searching: false,
                pageLength: 30,
                ordering: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('admin-prod-datatables') }}',
                    data: {
                        "category_id": cat,
                        "keyw": keyw
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'photo',
                        name: 'photo',
                        "render": function(data) {
                            return `<img src="{{ asset('assets/images/products/${data}') }}">`;
                        }
                    },
                    {
                        data: 'type',
                        name: 'type'
                    },
                    {
                        data: 'stock',
                        name: 'stock'
                    },
                    {
                        data: 'price',
                        name: 'price'
                    },
                    {
                        data: 'status',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'select',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'action',
                        searchable: false,
                        orderable: false
                    }

                ],
                language: {
                    processing: '<img src="{{ asset('assets/images/' . $gs->admin_loader) }}">'
                },
                drawCallback: function(settings) {
                    $('.select').niceSelect();
                }
            });
        }

        products();
        {{-- var table = $('#geniustable').DataTable({ --}}
        {{--	   ordering: false, --}}
        {{--       processing: true, --}}
        {{--       serverSide: true, --}}
        {{--       ajax: { --}}
        {{--	   	url:'{{ route('admin-prod-datatables')}}', --}}
        {{--	   	data:{ --}}
        {{--	   		"category_id":1 --}}
        {{--		} --}}
        {{--	   }, --}}
        {{--       columns: [ --}}
        {{--                { data: 'name', name: 'name' }, --}}
        {{--                { data: 'type', name: 'type' }, --}}
        {{--                { data: 'stock', name: 'stock' }, --}}
        {{--                { data: 'price', name: 'price' }, --}}
        {{--                { data: 'status', searchable: false, orderable: false}, --}}
        {{--    			{ data: 'action', searchable: false, orderable: false } --}}

        {{--             ], --}}
        {{--        language : { --}}
        {{--        	processing: '<img src="{{asset('assets/images/'.$gs->admin_loader)}}">' --}}
        {{--        }, --}}
        {{--		drawCallback : function( settings ) { --}}
        {{--				$('.select').niceSelect(); --}}
        {{--		} --}}
        {{--    }); --}}

        $(function() {
            $(".btn-area").append('<div class="col-sm-4 table-contents">' +
                '<a class="add-btn" href="{{ route('admin-prod-types') }}">' +
                '<i class="fas fa-plus"></i> <span class="remove-mobile">{{ __('Add New Product') }}<span>' +
                '</a>' +
                '</div>');
        });



        {{-- DATA TABLE ENDS --}}



        let selectProductList = [];

        $(document).on('change', '.product_multiple_selectoin_id', function() {
            var id = $(this).val();
            if ($(this).is(':checked')) {
                console.log("checked", id);
                // get value of product id and push it in array
                selectProductList.push(id);
                $('.product_multiple_selectoin_id').each(function() {
                    if ($(this).val() == id) {
                        $(this).prop('checked', true);
                    }
                });
            } else {
                // remove id from array when uncheck the checkbox
                selectProductList.splice(selectProductList.indexOf(id), 1);
                $('.product_multiple_selectoin_id').each(function() {
                    if ($(this).val() == id) {
                        $(this).prop('checked', false);
                    }
                });
            }
        });

        $(document).on('click', '#bulk_active_btn', function() {
            if (selectProductList.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin-prod-multiple-status') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        productIds: selectProductList,
                        type: 'active'
                    },
                    success: function(data) {
                        selectProductList = []
                      $('#geniustable').DataTable().ajax.reload();
                        // alert("success");
                    }
                });
            } else {
                alert("Please select at least one product");
            }
        });


        $(document).on('click', '#bulk_de_active_btn', function() {
            if (selectProductList.length > 0) {
                $.ajax({
                    type: "POST",
                    url: "{{ route('admin-prod-multiple-status') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        productIds: selectProductList,
                        type: 'deactive'
                    },
                    success: function(data) {
                        selectProductList = []
                      $('#geniustable').DataTable().ajax.reload();

                        // alert("success");
                    }
                });
            } else {
                alert("Please select at least one product");
            }
        });
    </script>


    <script type="text/javascript">
        // Gallery Section Update

        $(document).on("click", ".set-gallery", function() {
            var pid = $(this).find('input[type=hidden]').val();
            $('#pid').val(pid);
            $('.selected-image .row').html('');
            $.ajax({
                type: "GET",
                url: "{{ route('admin-gallery-show') }}",
                data: {
                    id: pid
                },
                success: function(data) {
                    if (data[0] == 0) {
                        $('.selected-image .row').addClass('justify-content-center');
                        $('.selected-image .row').html('<h3>{{ __('No Images Found.') }}</h3>');
                    } else {
                        $('.selected-image .row').removeClass('justify-content-center');
                        $('.selected-image .row h3').remove();
                        var arr = $.map(data[1], function(el) {
                            return el
                        });

                        for (var k in arr) {
                            $('.selected-image .row').append('<div class="col-sm-6">' +
                                '<div class="img gallery-img">' +
                                '<span class="remove-img"><i class="fas fa-times"></i>' +
                                '<input type="hidden" value="' + arr[k]['id'] + '">' +
                                '</span>' +
                                '<a href="' + '{{ asset('assets/images/galleries') . '/' }}' + arr[
                                    k]
                                ['photo'] + '" target="_blank">' +
                                '<img src="' + '{{ asset('assets/images/galleries') . '/' }}' +
                                arr[
                                    k]['photo'] + '" alt="gallery image">' +
                                '</a>' +
                                '</div>' +
                                '</div>');
                        }
                    }

                }
            });
        });


        $(document).on('click', '.remove-img', function() {
            var id = $(this).find('input[type=hidden]').val();
            $(this).parent().parent().remove();
            $.ajax({
                type: "GET",
                url: "{{ route('admin-gallery-delete') }}",
                data: {
                    id: id
                }
            });
        });

        $(document).on('click', '#prod_gallery', function() {
            $('#uploadgallery').click();
        });


        $("#uploadgallery").change(function() {
            $("#form-gallery").submit();
        });

        $(document).on('submit', '#form-gallery', function() {
            $.ajax({
                url: "{{ route('admin-gallery-store') }}",
                method: "POST",
                data: new FormData(this),
                dataType: 'JSON',
                contentType: false,
                cache: false,
                processData: false,
                success: function(data) {
                    if (data != 0) {
                        $('.selected-image .row').removeClass('justify-content-center');
                        $('.selected-image .row h3').remove();
                        var arr = $.map(data, function(el) {
                            return el
                        });
                        for (var k in arr) {
                            $('.selected-image .row').append('<div class="col-sm-6">' +
                                '<div class="img gallery-img">' +
                                '<span class="remove-img"><i class="fas fa-times"></i>' +
                                '<input type="hidden" value="' + arr[k]['id'] + '">' +
                                '</span>' +
                                '<a href="' + '{{ asset('assets/images/galleries') . '/' }}' + arr[
                                    k]
                                ['photo'] + '" target="_blank">' +
                                '<img src="' + '{{ asset('assets/images/galleries') . '/' }}' +
                                arr[
                                    k]['photo'] + '" alt="gallery image">' +
                                '</a>' +
                                '</div>' +
                                '</div>');
                        }
                    }

                }

            });
            return false;
        });


        // Gallery Section Update Ends


        // input type = checkbox class product_multiple_selectoin_id for multiple selection of products get lisener when check 
        // and uncheck the checkbox
    </script>
@endsection
