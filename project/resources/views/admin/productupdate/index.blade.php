@extends('layouts.admin')
@section('styles')
    <style>
        .page-item.active .page-link {
            background-color: #00778F;
            border-color: #00778F;
        }
        .page-link, .page-link:hover{
            color: #00778F;
        }
        .primary-bg{
            background-color: #00778F;
            color: #fff;
            border-color: #00778F;
        }

    </style>
@endsection
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
                            <a href="{{ route('admin-prod-bulk-index') }}">{{ __("Products Update") }}</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="product-area">
            <div class="row">
                <div class="col-lg-12">
                    <div class="mr-table">
                        <form class="form-inline form-validate" action="{{route('admin-prod-bulk-index')}}" enctype="multipart/form-data">

                            <div class="form-group">
                                <h6 style="font-weight: bold; padding:0px 5px; ">Filter By Category:</h6>
                            </div>

                            <div class="input-group">
                                <select class="form-control" name="category_id" style="min-width: 200px">
                                    <option value="">Select Category</option>
                                   @foreach($categories as $category)
                                        <option value="{{$category->id}}">{{$category->name}}</option>
                                   @endforeach
                                </select>
                            </div>

                            <div class="input-group">
                                <input type="text" name="product" class="form-control" placeholder="Products">
                            </div>

                            <button type="submit" class="btn btn-info primary-bg">Search</button>
                            <a href="{{route('admin-prod-bulk-index')}}" class="btn btn-outline-info">Clear Search</a>
                            <button type="button" class="btn btn-info primary-bg updateAll ml-auto" style="">Update Products</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="mr-table allproduct">

                        @include('includes.admin.form-success')
                        <form action="{{route('admin-prod-bulk-update')}}" method="post" id="update_form">
                            @csrf
                            <div class="table-responsiv">
                                <table id="geniustable" class="table table-hover dt-responsive" cellspacing="0"
                                       width="100%">
                                    <thead>
                                    <tr>
                                        <th>{{ __("SL") }}</th>
                                        <th>{{ __("Image") }}</th>
                                        <th>{{ __("Product Details") }}</th>
                                        <th>{{ __("Added/Last Modified Date") }}</th>
                                        <th>{{ __("New Product Price") }}</th>
                                        <th>{{ __("Product Stock Quantity") }}</th>
                                        <th>{{ __("Product Stock") }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                    @php
                                        $i=1;
                                    @endphp
                                    @foreach($products as $product)

                                        <tr>
                                            <td>{{$i++}}</td>
                                            <td><img src="{{asset('assets/images/products/'.$product->photo)}}" alt="" width="100px" height="100px"></td>
                                            <td width="30%">

                                                <strong>{{$product->name}} </strong><br>
                                                <strong>Product Type:</strong>{{$product->product_type}}
                                                <br>
                                                <strong>Manufacturer:</strong> {{$product->user?$product->user->name:''}}<br>
                                                <strong>Price: </strong>
                                                <strong class="badge primary-bg">
                                                    {{$product->showPrice()}}
                                                </strong>
                                                <br>
                                                <strong>Weight
                                                    : </strong> {{$product->measure}}

                                            </td>
                                            <td>
                                                <strong>Added Date
                                                    : </strong> {{$product->created_at}}<br>
                                                <strong>Modified Date
                                                    : </strong> {{$product->updated_at}}
                                            </td>
                                            <td>
                                                <input type="hidden" name="product_id[]" value="{{$product->id}}">
                                                <input type="number" class="form-control" name="product_price[{{$product->id}}]" value="{{$product->price * $curr->value}}">
                                            </td>

                                            <td>
                                                <input type="number" class="form-control" name="product_stock[{{$product->id}}]" value="{{$product->stock}}">
                                            </td>
                                            <td>
                                                <select name="product_inventory[{{$product->id}}]" class="form-control">
                                                    <option value="1" {{!$product->emptyStock()==true?'selected':''}}>
                                                        In
                                                    </option>
                                                    <option value="0" {{$product->emptyStock()==true?'selected':''}}>
                                                        Out
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                    @endforeach


                                    </tbody>
                                </table>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            @if(count($products)>0)
                <div class="row">
                    <div class="col-lg-10">
                        <div class="float-right">
                            {{$products->appends(\Illuminate\Support\Facades\Request::all())->links()}}
                        </div>
                    </div>
                    <div class="col-lg-2">
                        <form action="{{route('admin-prod-bulk-index')}}" class="form-inline">
                            <input type="number" name="page" class="form-control" placeholder="Go to Page" style="width: 60%" required>
                            <input type="hidden" name="category_id" class="form-control" value="{{$category_id??''}}">
                            <button type="submit" class="btn btn-info primary-bg" style="margin-bottom: 10px;"><i class="fa fa-arrow-circle-right"></i></button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection



@section('scripts')

    <script type="text/javascript">
        $(document).ready(function () {
            $('.updateAll').on('click',function () {
                $('form#update_form').submit();
            });
            // $("select[name='category_id']").on('change',function () {
            //     var category_id=$(this).val();
            //     if(category_id){
            //         $("input[name='category_id']").val(category_id);
            //     }else{
            //         $("input[name='category_id']").val('');
            //     }
            // })
        });


    </script>
@endsection   