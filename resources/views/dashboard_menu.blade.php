<div class="row">
            <div class="col-lg-3 col-6">
                <a href="{{ action('ProductController@stockIn') }}" >
                    <div class="small-box bg-success">
                        <div class="inner">
                            <p>Stock</p>
                            <h3>In</h3>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-6">
                <a href="{{ action('ProductController@stockOut') }}" >
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <p>Stock</p>
                            <h3>Out</h3>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-6">
                <a href="{{ route('products.stock.history') }}">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <p>Stock</p>
                            <h3>History</h3>
                        </div>
                        <div class="icon">
                            <i class="fas fa-history"></i>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-lg-3 col-6">
                <a href="{{ route('products.categories') }}">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <p>Product</p>
                            <h3>Categories</h3>
                        </div>
                        <div class="icon">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>