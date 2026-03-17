<footer class="pc-footer">
    <div class="footer-wrapper container-fluid">
        <div class="row">
            <div class="col-sm my-1">
                <p class="m-0">
                    {{ config('app.name') }} v1.0 &#9829; crafted with care by <a href="https://aykonsult.com/about.html" target="_blank">AYKON INFORMATION SYSTEMS</a>. All rights reserved @ {{ date('Y') }}.
                </p>
            </div>
            <div class="col-auto my-1">
                <ul class="list-inline footer-link mb-0">
                    <li class="list-inline-item">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#">Support</a>
                    </li>
                    <li class="list-inline-item">
                        <a href="#">Privacy Policy</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</footer>