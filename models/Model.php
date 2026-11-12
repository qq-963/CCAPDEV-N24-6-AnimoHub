<?php
class Model {
    protected $db;

    function __construct($db) { $this->db = $db; }

    // run a query, params go after the sql
    protected function q($sql, ...$p) {
        $st = $this->db->prepare($sql);
        $st->execute($p);
        return $st;
    }
}
