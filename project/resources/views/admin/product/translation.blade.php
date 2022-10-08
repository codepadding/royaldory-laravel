@extends('layouts.admin')
@section('styles')
    <style>
        .btn-goto{
            background: #2d3274;
        }
        .page-item.active .page-link{
            background-color:#2d3274;
            border-color:#2d3274;
        }
        .page-link{
            color:#2d3274;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote.min.css" rel="stylesheet">
@stop

@section('content')
    <input type="hidden" id="headerdata" value="{{ __("PRODUCT") }}">
    <div class="content-area">
        <div class="mr-breadcrumb">
            <div class="row">
                <div class="col-lg-12">
                    <h4 class="heading">{{ __("Products") }}</h4>
                    <ul class="links">
                        <li>
                            <a href="{{ route('admin.dashboard') }}">{{ __("Dashboard") }} </a>
                        </li>
                        <li>
                            <a href="javascript:;">{{ __("Products") }} </a>
                        </li>
                        <li>
                            <a href="{{ route('prod-translation-index') }}">{{ __("Product Translation") }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card mb-3" >
            <div class="card-body">
                <div class="row mt-3" width="60%">
                    <div class="col-lg-12">
                        <form action="{{route('prod-translation-index')}}" method="GET">
                            <div class="form-row">
                                <div class="col">
                                    {{--												<label for="">Select Category</label>--}}
                                    <select name="category_id" class="select2" id="">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $cats)
                                            <option value="{{$cats->id}}">{{$cats->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    {{--<label for="">Select Product</label>--}}
                                    <input type="text" name="product" class="form-control" placeholder="Product Name">
                                </div>
                                <div class="col">
                                    <select name="language" class="form-control" id="" required >
                                        <option value="">Select Translation Language</option>
                                        @foreach($languages as $lang)
                                            <option value="{{$lang->id}}">{{$lang->language}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-primary mb-2 add-btn">Search</button>
                                </div>
                                <div class="pull-right">
                                    <button type="button" id="submit-btn" class="btn btn-primary mb-2 add-btn">Update Language</button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>

        </div>

        <div class="product-area">
            <div class="row">
                <div class="col-lg-12">
                    <div class="mr-table allproduct">
                        @if(session()->has('success'))
                            <div class="alert alert-success validation alert-dismissible fade show">
                                <button type="button" class="close alert-close"><span>×</span></button>
                                <p class="text-left">{{session()->get('success')}}</p>
                            </div>
                        @endif
                        <form action="{{route('prod_translation_update')}}" method="POST" id="price_update_form">

                            <input type="hidden" name="_token" value="{{@csrf_token()}}">
                            <input type="hidden" name="keyword" value="{{$language? $language->keyword:''}}">
                            <div class="table-responsiv">
                            <table id="geniustable" class="table table-hover dt-responsive dataTable " cellspacing="0" width="100%">
                                <thead>
                                <tr class="text-center">
                                    <th width="5%">{{ __("Image") }}</th>
                                    <th width="15%">{{ __("Name")}} {{$language? "($language->language)":'(Translated)' }}</th>
                                    <th>{{ __("Description")}} {{$language? "($language->language)":'(Translated)'}}</th>
                                </tr>
                                </thead>
                                <tbody>

                                    @foreach($products as $product)
                                        <input type="hidden" name="id[]" value="{{$product->id}}">
                                        <tr>
                                            <td><img width="80px" height="80px" src="{{asset('assets/images/products/'.$product->photo)}}" class="img-responsive" alt=""></td>
                                            <td><textarea name="name[]" class="form-control">{{$product->getTranslation('name',$language->keyword)}}</textarea></td>
                                            <td><textarea name="details[]" class="form-control summernote" >{{$product->getTranslation('details',$language->keyword)}}</textarea></td>
{{--                                            <td><textarea name="details[]" class="form-control nic-edit" ></textarea></td>--}}
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-10">
                    <div class="ml-4">
                       @if($products)
                            {{$products->appends(\Illuminate\Support\Facades\Request::all())->links("pagination::bootstrap-4")}}
                       @endif
                    </div>

                </div>
                <div class="col-lg-2">
                    @if($products)
                    <form action="{{route('prod-translation-index')}}" class="form-inline">
                        <input type="number" name="page" class="form-control" placeholder="Go to Page" style="width: 60%" required="">
                        <button type="submit" class="btn btn-primary btn-goto" style="margin-bottom: 10px"><i class="fa fa-arrow-circle-right"></i></button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection



@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.16/dist/summernote.min.js"></script>
    <script>
        // $('#geniustable').DataTable({});
        $(document).ready(function () {
            $('#submit-btn').click(function () {
                $('#price_update_form').submit();
            });
            $('.summernote').summernote();
        });
    </script>
@endsection