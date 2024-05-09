<?php

namespace App\Models;

use CodeIgniter\Model;

class Post extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'posts';
    protected $primaryKey       = 'id';
    protected $allowedFields    = [
        'author_id',
        'category_id',
        'title',
        'content',
        'featured_image',
        'visibility',
        'expiration_date',
        'notification_sent'
    ];

    /**
     * Fetch posts that are expiring soon.
     * 
     * @param string $date Today's date or any specific date from which the 30-day range is calculated.
     * @return array of Post objects that are expiring within the next 30 days.
     */
    public function getExpiringPosts($date) {
        $date = new \DateTime($date);
        $expiringDate = $date->modify('+30 days')->format('Y-m-d');

        return $this->asArray()
                    ->where('expiration_date <=', $expiringDate)
                    ->where('expiration_date >=', date('Y-m-d'))
                    ->findAll();
    }
}
