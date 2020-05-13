<?php
/**
 * Created by PhpStorm.
 * User: karl
 * Date: 2019-06-26
 * Time: 10:36
 */

namespace Moycroft\API\internal\CDN;


use Moycroft\API\internal\CDN\Bucket\Bucket;
use Moycroft\API\internal\mysql\Connect;
use function Moycroft\API\internal\reporting\report\__crash;
use function Moycroft\API\internal\reporting\report\__error;
use function Moycroft\API\internal\reporting\report\__infoSH;

class CDN
{
    function findBucket($id){
        $bucketExists = false;
        if ($bucketExists){
            return new Bucket($id);
        }
    }
}