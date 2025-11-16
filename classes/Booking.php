<?php
class Booking {
    private $id;
    private $customer;
    private $date;
    private $time;
    private $room;
    private $status;

    public function __construct($id, $customer, $date, $time, $room, $status) {
        $this->id = $id;
        $this->customer = $customer;
        $this->date = $date;
        $this->time = $time;
        $this->room = $room;
        $this->status = $status;
    }

    public function getId() { return $this->id; }
    public function getCustomer() { return $this->customer; }
    public function getDate() { return $this->date; }
    public function getTime() { return $this->time; }
    public function getRoom() { return $this->room; }
    public function getStatus() { return $this->status; }

    public function setStatus($newStatus) {
        $this->status = $newStatus;
    }
}
