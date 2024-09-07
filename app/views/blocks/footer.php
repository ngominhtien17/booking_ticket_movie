<footer class="text-center text-lg-start text-muted">
    <div class="container" style="color: var(--text-color)">
        <section class="p-3">
            <div class="container text-center text-md-start mt-5">
                <div class="row mt-3">
                    <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                        <h6 class="text-uppercase fw-bold mb-4">
                            <i class="fas fa-gem me-3"></i><span style="color: var(--text-color)">K25Cinema</span>
                        </h6>
                        <p>
                            Chất lượng đặt lên hàng đầu
                        </p>
                    </div>
                    <div class="col-md-2 col-lg-2 col-xl-2 mx-auto mb-4">
                        <h6 class="text-uppercase fw-bold mb-4">
                            Hệ thống rạp
                        </h6>

                        <div class="ListTheater"></div>
                    </div>
                    <div class="col-md-3 col-lg-2 col-xl-2 mx-auto mb-4">
                        <h6 class="text-uppercase fw-bold mb-4">
                            Chính sách và quy định
                        </h6>
                        <p>
                            <a href="#!" class="text-reset text-decoration-none">Quy định chung</a>
                        </p>
                        <p>
                            <a href="#!" class="text-reset text-decoration-none">Điều khoản giao dịch</a>
                        </p>
                    </div>
                    <div class="col-md-4 col-lg-3 col-xl-3 mx-auto mb-md-0 mb-4">
                        <h6 class="text-uppercase fw-bold mb-4">
                            Liên hệ
                        </h6>
                        <p>
                            <i class="fas fa-home me-3"></i>
                            Nguyễn Thị Thập, Quận 7, TP.HCM
                        </p>
                        <p>
                            <i class="fas fa-envelope me-3"></i>
                            k25cinema@gmail.com
                        </p>
                        <p>
                            <i class="fas fa-phone me-3"></i>
                            + 033 6986 319
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <div class="text-center p-4">
            © 2023 Copyright: K25Cinema
        </div>
    </div>
</footer>
<script>
    fetch(`<?php echo _WEB_ROOT ?>/theater/getAll`)
        .then(res => res.json())
        .then(data => {
            $(".ListTheater").children().remove();
            let p = "";
            data.forEach(element => {
                p += `<p>
                            <a href="#!" class="text-reset text-decoration-none">${element.name}</a>
                    </p>`;
            });
            $(".ListTheater").append(p);
        })
        .catch(err => console.log(err));
</script>