<?php
class TheaterModel extends BaseModel
{
	protected $_table = 'rap';

	public function getAll()
	{
		$res = $this->db->query("select * from rap");
		$data = [];
		while ($row = $res->fetch_assoc()) {
			$data[] = $row;
		}
		return json_encode($data);
	}
	public function insert($name, $address) {
		$res = $this->db->prepare("insert into rap (name, address) values(?,?)");
		$res->bind_param("ss", $name, $address);
		$res->execute();
		return $res->affected_rows;
	}
	public function getByIdRap($id_rap)
	{
    	$res = $this->db->prepare("SELECT * FROM rap WHERE id_rap = ?");
    	$res->bind_param("i", $id_rap);
    	$res->execute();
    	$data = $res->get_result()->fetch_assoc();
    	return json_encode($data);
	}
}
