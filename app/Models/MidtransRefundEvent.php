<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MidtransRefundEvent extends Model
{
    protected $table = 'midtrans_refund_events';
    protected $primaryKey = 'id_midtrans_refund_event';
    public const CREATED_AT = 'diterima_pada';
    public const UPDATED_AT = null;
    protected $fillable = ['id_pembayaran', 'event_key', 'refund_provider_id', 'nominal_refund', 'nominal_refund_kumulatif'];
    protected $casts = ['diterima_pada' => 'datetime'];
}
