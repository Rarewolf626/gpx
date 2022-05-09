<?php
namespace GPX\Model\WordPress;

use Illuminate\Database\Eloquent\Model;

class Post extends Model {
    protected $table = 'wp_posts';
    protected $primaryKey = 'ID';
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = ['post_password'];
    protected $casts = [
        'ID' => 'integer',
        'post_author' => 'integer',
        'post_date' => 'datetime',
        'post_date_gmt' => 'datetime',
        'post_modified' => 'datetime',
        'post_modified_gmt' => 'datetime',
        'comment_count' => 'integer',
    ];
}
