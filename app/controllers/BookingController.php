<?php
class BookingController extends BaseController
{
	public $model_booking, $data = [],$customerDetail = [];
	public function __construct()
	{
		$this->model_booking = $this->model("BookingModel");
	}
	public function index($time, $type, $date, $idsc, $room)
	{
		if (!isset($_SESSION['user'])) {
			header("Location: " . _WEB_ROOT . "/user/login");
			exit();
			// return;
		}
		// Lưu thông tin của suất chiếu mà người dùng chọn vào session
		$_SESSION['time-booking'] = $time;
		$_SESSION['type-booking'] = $type;
		$_SESSION['date-booking'] = $date;
		$_SESSION['id-booking'] = $idsc;
		// echo $_SESSION['id-booking'];
		$ghe = json_decode($this->model("SeatModel")->getAllByShowtime($idsc));
		$this->data['data_ghe'] = $ghe;
		$this->data['title_page'] = 'Chọn ghế';
		$this->data['link_css'] = "<link rel='stylesheet' href='" . _WEB_ROOT . "/public/assets/client/css/chonbapnuoc.css' />";
		$this->data['link_script'] = "<script src='" . _WEB_ROOT . "/public/assets/client/js/chonghe.js'></script>";
		$this->data['content'] = 'booking/chonghe';
		$id_phim_choosing = $_SESSION['id_phim_choosing'];
		$data = json_decode($this->model("FilmModel")->getDetail($id_phim_choosing));
		$this->data['data_pass'] = $data;
		$this->render("layout/client_layout", $this->data);
	}
	public function chooseFoods()
	{
		$this->data['title_page'] = 'Chọn bắp nước';
		$this->data['link_css'] = "<link rel='stylesheet' href='" . _WEB_ROOT . "/public/assets/client/css/chonbapnuoc.css' />";
		$this->data['link_script'] = "<script src='" . _WEB_ROOT . "/public/assets/client/js/chonbapnuoc.js'></script>";
		$info = $this->model("ShowtimeModel")->detail($_SESSION['id-booking']);
		$this->data['info'] = json_decode($info);
		$this->data['content'] = 'booking/chonbapnuoc';
		$this->data['data_pass'] = json_decode($this->model("FoodModel")->getAll());
		$this->render("layout/client_layout", $this->data);
	}
	public function checkout()
	{
		$data = json_decode(file_get_contents('php://input'), true);
		// echo $data;
		$id_khachhang = json_decode($_SESSION['user'])->id_khachhang;
		$id_suatchieu = $_SESSION['id-booking'];
		$num_ticket = $data['lichsu']['soluongve'];
		$seat = $data['lichsu']['ghedat'];
		$total_price = $data['lichsu']['tongtien'];
		date_default_timezone_set('Asia/Bangkok'); // Set the timezone to GMT+7
		$booking_date = date('H:i:s d/m/Y'); // format the date and time as desired
		// Lay infor khach hang
		$inforUser = json_decode($this->model("UserModel")->getDetail($id_khachhang), true);
		$userName = $inforUser['name'];

		$xuatChieu = json_decode($this -> model("ShowtimeModel") -> detail($id_suatchieu), true);
		$id_film = $xuatChieu['id_phim']; // Lay ten phim tu Id film
		$id_rap = $xuatChieu['id_rap']; // Lay thong tin cua rap phim
		$date_film = $xuatChieu['date_show']; // ngay chieu
		$time_film = $xuatChieu['time_show']; // gio chieu
		$id_room = $xuatChieu['id_phongchieu']; // lay phong chieu

		$inforFilm = json_decode($this -> model("FilmModel") -> getDetail($id_film), true);
		$nameFilm = $inforFilm['name_phim'];

		$inforRap = json_decode($this -> model("TheaterModel") -> getByIdRap($id_rap), true);
		$name_rap = $inforRap['name'];
		$address_rap = $inforRap['address'];

		$inforPhong = json_decode($this -> model("ShowroomModel") -> getByIdPhong($id_room), true);
		$tenPhong = $inforPhong['name_phongchieu'];

		// Insert vào lịch sử
		$this->model("HistoryModel")->insert($id_khachhang, $id_suatchieu, $num_ticket, $seat, $total_price);
		$new_id = $this->model("HistoryModel")->getLastInsertedId();
        //Chuyen doi QR
        $Qr = isset($_GET['data']) ? $_GET['data'] : $new_id;

        // Tạo URL của mã QR sử dụng API QR Code Generator
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($Qr);

		$seats = json_decode($data['data_datcho']);
		// Đánh dấu các ghế đã được đặt
		foreach ($seats as $item) {
			$this->model("ShowtimeSeatModel")->update($item->id_suatchieu, $item->id_ghe, 1);
		}
		// Update điểm cho khách hàng
		$this->model("UserModel")->updatePoint($id_khachhang, 10 * $num_ticket);
		//Đặt vé xong chuyển người dùng đến trang thông tin cá nhân để xem vé
		
		$url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($new_id);
		// $this->mail->addAddress();
		require _DIR_ROOT . "/app/helpers/Mailer.php";
		try {
			$to = json_decode($_SESSION['user'])->email;
			$title = "Thông báo đặt vé thành công";
			$subject = mb_encode_mimeheader($title, "UTF-8", "B");
			$body = "<!DOCTYPE html>
            <html lang=\"en\">
            <head>
                <meta charset=\"UTF-8\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <title>Document</title>
                <style>
                @import url(\"https://fonts.googleapis.com/css2?family=Staatliches&display=swap\");
                @import url(\"https://fonts.googleapis.com/css2?family=Nanum+Pen+Script&display=swap\");
                
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body,
                html {
                    display: grid;
                    background: black;
                    color: black;
                    font-size: 10px;
                    letter-spacing: 0.1em;
                }
                
                .ticket {
                    margin: auto;
                    display: flex;
                    background: white;
                    box-shadow: rgba(0, 0, 0, 0.3) 0px 19px 38px, rgba(0, 0, 0, 0.22) 0px 15px 12px;
                }
                
                .left {
                    display: flex;
                }
                
                .image {
                    height: 200px;
                    width: 200px;
                    background-size: contain;
                    opacity: 0.9;
                }
                
                .admit-one {
                    position: absolute;
                    color: darkgray;
                    height: 200px;
                    padding: 0 5px;
                    letter-spacing: 0.15em;
                    display: flex;
                    text-align: center;
                    justify-content: space-around;
                    writing-mode: vertical-rl;
                    transform: rotate(-180deg);
                }
                
                .admit-one span:nth-child(2) {
                    color: white;
                    font-weight: 800;
                }
                
                .left .ticket-number {
                    height: 200px;
                    width: 200px;
                    display: flex;
                    justify-content: flex-end;
                    align-items: flex-end;
                    padding: 10px;
                }
                
                .ticket-info {
                    padding: 5px 10px;
                    display: flex;
                    flex-direction: column;
                    text-align: center;
                    justify-content: space-between;
                    align-items: center;
                }
                
                .date {
                    border-top: 1px solid gray;
                    border-bottom: 1px solid gray;
                    padding: 5px 0;
                    font-weight: 700;
                    display: flex;
                    align-items: center;
                    justify-content: space-around;
                }
                
                .date span {
                    width: 90px;
                }
                
                .date span:first-child {
                    text-align: left;
                }
                
                .date span:last-child {
                    text-align: right;
                }
                
                .date .june-29 {
                    color: #d83565;
                    font-size: 10px;
                }
                
                .show-name {
                    font-size: 20px;
                    font-family: \"Nanum Pen Script\", cursive;
                    color: #d83565;
                }
                
                .show-name h1 {
                    font-size: 30px;
                    font-weight: 700;
                    letter-spacing: 0.1em;
                    color: #4a437e;
                }
                
                .time {
                    padding: 10px 0;
                    color: #4a437e;
                    text-align: center;
                    display: flex;
                    flex-direction: column;
                    gap: 10px;
                    font-weight: 500;
                }
                
                .time span {
                    font-weight: 500;
                    color: gray;
                }
                
                .left .time {
                    font-size: 14px;
                }
                
                
                
                
                .right {
                    width: 180px;
                    border-left: 1px dashed #404040;
                }
                
                .right .admit-one {
                    color: darkgray;
                }
                
                .right .admit-one span:nth-child(2) {
                    color: gray;
                }
                
                .right .right-info-container {
                    height: 200px;
                    padding: 10px 10px 10px 10px;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-around;
                    align-items: center;
                }
                
                .right .show-name h1 {
                    font-size: 18px;
                }
                
                .barcode {
                    height: 200px;
                }
                
                .barcode img {
                    height: 100%;
                }
                
                .right .ticket-number {
                    color: gray;
                }
                
                  </style>
            </head>
            <body>
            <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
            <tbody>
            <tr><td align=\"center\" style=\"min-width:512px;background-color:#f3f3f3\">
                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                    <tbody>
                    <tr>
                        <td align=\"center\" style=\"padding-bottom:0px\">
                            <table align=\"center\" width=\"512\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
                                <tbody>    
                                    <tr>
                                        <td align=\"center\" style=\"padding-top:10px;padding-bottom:15px\">
                                            <table width=\"95%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                <tbody>
                                                <tr>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align=\"center\" style=\"background-color:white\">
                                            <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                <tbody>
                                                <tr>
                                                    <td style=\"border-top:3px solid #ae2070;border-radius:4px 4px 0 0\">
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>                     
                                <tr>
                                    <td align=\"center\" style=\"padding-top:25px;padding-bottom:25px;background-color:white\">
                                    <div class=\"logo\">
                                    <div class=\"text header-text\">
                                        <span style=\"font-weight:bold\" class=\"name\">K25Cinema</span>
                                    </div>
                                </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\" style=\"background-color:white\">
                                        <table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                            <tbody>
                                            <tr><td>
                                                <img src=\"https://ci3.googleusercontent.com/meips/ADKq_Nb4_CdH6ivMV5KKC_7dZJeRIFgPMT36aYr6lv1KtFbbaYbkwwboMuqe4EtJr0sKtSO5FxCjpxamr-DPYaXx2jcj5jqq_Ao5gPpCNz1ur7UInVOcaKE=s0-d-e1-ft#https://cdn.mservice.com.vn/app/img/ota/email/banner_cinema.png\" width=\"100%\" alt=\"Ví điện tử MoMo\" style=\"object-fit:contain;display:block;border:0\" class=\"CToWUd a6T\" data-bit=\"iit\" tabindex=\"0\">
                                            </td>
                                            </tr></tbody>
                                        </table>
        
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\" style=\"background-color:white\">
                                        <table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                            <tbody>
                                            <tr><td align=\"left\" style=\"padding-top:25px;padding-bottom:25px;background-color:white\">
                                                <p style=\"margin:0 0 0 0;color:#303233;font-size:12px;margin:0;padding-bottom:10px\">
                                                    Xin chào <span style=\"color:#303233;font-size:12px;font-weight:bold\">$userName,</span>
                                                </p>
                                                <p style=\"margin:0 0 0 0;color:#303233;font-size:12px\">
                                                    <br> Cảm ơn bạn đã sử dụng dịch vụ của K25Cinema!<br>
                                                    K25Cinema xác nhận bạn đã đặt vé xem phim của <span style=\"font-weight:bold;font-size:12px\"><span class=\"il\">K25Cinema</span> $name_rap</span> thành
                                                    công lúc <span style=\"font-weight:bold;font-size:12px\">$booking_date .</span> <br> Chi tiết vé của bạn
                                                    như sau: </p>
                                            </td>
                                            </tr></tbody>
                                        </table>
                                    </td>                                   
                                </tr>
                                <td align=\"center\" style=\"background-color:white\">
                                    <table width=\"90%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:12px\">
                                        <tbody>
                                        <tr>
                                            <td align=\"left\" style=\"padding-top:25px;padding-bottom:0px;background-color:white;border:1px solid #e8e8e8;border-radius:12px\">
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"color:#303233;font-size:16px;padding-bottom:5px\">
                                                            <strong>Mã đặt vé</strong></td>
                                                    </tr>
                                                    <tr align=\"center\">
                                                        <td style=\"color:#eb2f96;font-size:20px;padding-bottom:10px\">
                                                            <strong>$new_id</strong></td>
                                                    </tr>
                                                    <tr align=\"center\">
                                                        <td align=\"center\">
                                                            <table border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:10px\">
                                                                <tbody>
                                                                <tr align=\"center\">
                                                                    <td align=\"center\" style=\"padding:2px;background-color:white;border:1px solid #e8e8e8;border-radius:12px\">
                                                                        

<div>
    <div class=\"right-info-container\">
        <div class=\"barcode\">
            <img src=\"$qrCodeUrl\" alt=\"QR code\">
        </div>
    </div>
</div>
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
    
    
                                                        </td>
                                                    </tr>
                                                    <tr align=\"center\">
                                                        <td style=\"color:#727272;font-size:12px;padding-bottom:15px\">Đem
                                                            mã Barcode này đến quầy giao dịch hoặc nhân viên soát vé để nhận vé
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:12px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"padding-left:12px;padding-right:12px\">
                                                            <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                                <tbody>
                                                                <tr align=\"center\">
                                                                    <td style=\"padding:0px 0px 1px 0px\" bgcolor=\"#e8e8e8\" width=\"100%\"></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:12px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"color:#727272;font-size:14px\">Thời gian chiếu</td>
                                                    </tr>
                                                    <tr align=\"center\">
                                                        <td style=\"color:#303233;font-size:14px\">
                                                            <strong>$time_film $date_film</strong></td>
                                                    </tr>
                                                    
                                                    </tbody>
                                                </table>
    
                                                <table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:12px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"padding:0px 0px 8px 0px\" bgcolor=\"#f9f9f9\" width=\"100%\"></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:8px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td>
                                                            <table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left:12px\">
                                                                <tbody>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        Phim
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\">
                                                                    <td style=\"font-weight:bold;color:#303233\">
                                                                        $nameFilm
                                                                    </td>
                                                                </tr>
                                                                
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
    
                                                    </tbody>
                                                </table>
    
    
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"vertical-align:top\">
                                                            <table width=\"150\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left:12px;margin-right:12px\">
                                                                <tbody>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        Phòng Chiếu
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#303233;font-size:14px\">
                                                                        $tenPhong
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                        <td style=\"vertical-align:top\">
                                                            <table width=\"150\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left:10px;margin-right:10px\">
                                                                <tbody>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        Số Vé
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#303233;font-size:14px\">
                                                                        $num_ticket
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                        <td style=\"vertical-align:top\">
                                                            <table width=\"100%\" border=\"0\" align=\"right\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left:12px;margin-right:12px\">
                                                                <tbody>
                                                                <tr align=\"left\" style=\"margin-right:12px\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        Số Ghế
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\" style=\"margin-right:12px\">
                                                                    <td style=\"color:#303233;font-size:14px\">
                                                                        $seat
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
    
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:12px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"padding-left:12px;padding-right:12px\">
                                                            <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                                <tbody>
                                                                <tr align=\"center\">
                                                                    <td style=\"padding:0px 0px 1px 0px\" bgcolor=\"#e8e8e8\" width=\"100%\"></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:8px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td>
                                                            <table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left:12px\">
                                                                <tbody>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        Thức ăn kèm
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\"><td style=\"font-weight:bold;color:#303233\">Không</td></tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
    
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:12px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"padding-left:12px;padding-right:12px\">
                                                            <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                                <tbody>
                                                                <tr align=\"center\">
                                                                    <td style=\"padding:0px 0px 1px 0px\" bgcolor=\"#e8e8e8\" width=\"100%\"></td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-bottom:8px\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td>
                                                            <table width=\"100%\" border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\"margin-left:12px\">
                                                                <tbody>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        Rạp chiếu
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\">
                                                                    <td style=\"font-weight:bold;color:#303233\">
                                                                        <span class=\"il\">K25Cinema</span> $name_rap
                                                                    </td>
                                                                </tr>
                                                                <tr align=\"left\">
                                                                    <td style=\"color:#727272;font-size:14px\">
                                                                        <span class=\"il\"></span> $address_rap
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
    
                                                <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                    <tbody>
                                                    <tr align=\"center\">
                                                        <td style=\"background-color:#f0f0f0;padding-top:22px;padding-bottom:22px;border-bottom-left-radius:12px;border-bottom-right-radius:12px\">
                                                            <table width=\"100%\" border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                                                                <tbody>
                                                                <tr align=\"center\">
                                                                    <td align=\"left\" style=\"color:#303233;font-size:14px;padding-left:12px\">
                                                                        Tổng tiền
                                                                    </td>
                                                                    <td align=\"right\" style=\"color:#303233;font-size:20px;font-weight:bold;padding-right:12px\">
                                                                        $total_price
                                                                    </td>
                                                                </tr>
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                        </td></tr></tbody>
                                    </table>
                                </td>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                </td>
            </tr>
            </tbody>
        </table>
            </body>
            </html>" ;

			Mailer::getInstance()->sendMail($to, $subject, $body);
		} catch (Exception $e) {
		}
		echo _WEB_ROOT . "/user";
	}
}
