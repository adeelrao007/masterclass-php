<?php



class OrderModel
{
    protected $table = 'orders';

    protected $fillable = [
        'id',
        'status',
        // other fields...
    ];

    public $incrementing = false;
    protected $keyType = 'string';
}