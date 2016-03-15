<?php
/**
 * Data class for Social Capital Plugin
 *
 * PHP version 5
 *
 * @category Data
 * @package  StatusNet
 * @author   Stéphane Bérubé <chimo@chromic.org>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://github.com/chimo/social-analytics
 *
 * StatusNet - the distributed open-source microblogging tool
 * Copyright (C) 2009, StatusNet, Inc.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.     See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('GNUSOCIAL')) { exit(1); }

/**
 * Data class for Social Analytics stats
 *
 * We use the DB_DataObject framework for data classes in StatusNet. Each
 * table maps to a particular data class, making it easier to manipulate
 * data.
 *
 * Data classes should extend Memcached_DataObject, the (slightly misnamed)
 * extension of DB_DataObject that provides caching, internationalization,
 * and other bits of good functionality to StatusNet-specific data classes.
 *
 * @category Data
 * @package  StatusNet
 * @author   Stéphane Bérubé <chimo@chromic.org>
 * @license  http://www.fsf.org/licensing/licenses/agpl.html AGPLv3
 * @link     http://github.com/chimo/SocialAnalytics
 *
 * @see      DB_DataObject
 */
class SocialCapitalIndex extends Managed_DataObject
{
    /**
     * Satisfy Managed_DataObject
     */
    public static function schemaDef()
    {
        return array();
    }

    static function getIndex($profile_id)
    {
        
        $index = 0;

        $sci = new Social_capital();

        // ya tiene un registro anterior?
        if(!$sci->get($profile_id)) {
            //no
            $sc = self::init($profile_id);
            $index = $sc->index();

            $sci->social_index = $index;

            $sci->insert();
            //si
        } else {
            
            $updated = new DateTime($sci->updated);
            
            //esta actualizado?
            if($updated->diff(new DateTime())->h > 24) {
                //no
                $sc = self::init($profile_id);
                $index = $sc->index();

                $sci->social_index = $index;

                $sci->update();
                
            } else {
                // si
                $index = $sci->social_index;
            }
        }

        return $index;
    }

    /**
     * TODO: Document
     */
    static function init($profile_id)
    {
        $sc = new SocialCapitalIndex();
        $sc->profile_id = $profile_id;
        $sc->ttl_notices = 0;
        $sc->ttl_followers = 0;
        $sc->ttl_following = 0;

        $sc->ttl_mentions = 0; //replies
        $sc->ttl_faved = 0;
        $sc->ttl_shares = 0;
        $sc->ttl_shared = 0;

        $sc->ttl_noticesFromBlog = 0;



        $user = User::getKV('id', $profile_id);

        if (!empty($user)) {

            $profile = $user->getProfile();

            $sc->ttl_notices = 0;
            $sc->ttl_replies = 0;
            $sc->ttl_bookmarks = 0;

            $sc->ttl_notices = $profile->noticeCount();

            // Hosts you are following
            $sc->ttl_following = $profile->subscriptionCount();

            // Hosts who follow you
            $sc->ttl_followers = $profile->subscriberCount();

            // People who mentioned you
            $sc->ttl_mentions = SocialCapitalIndex::mentionsCount($profile->id);

            $sc->ttl_faved = SocialCapitalIndex::favesCount($profile->id);

            $sc->ttl_shares = SocialCapitalIndex::sharesCount($profile->id);

            $sc->ttl_shared = SocialCapitalIndex::sharedCount($profile->id);

            $sc->ttl_noticesFromBlog = SocialCapitalIndex::noticesFromBlogCount($profile->id);

        }

        return $sc;    
    }

    function index()
    {
        $emision = ($this->ttl_notices + $this->ttl_shares) * 2;
        $adhesion = ($this->ttl_followers + $this->ttl_faved) * 5;
        $participacion = ($this->ttl_mentions + $this->ttl_following + $this->ttl_shared) * 8;
        $interaccion = $this->ttl_noticesFromBlog * 50;
        return round( ($emision + $adhesion + $participacion + $interaccion) , 2);
    }

    function mentionsCount($profile_id)
    {
        $c = Cache::instance();

        if (!empty($c)) {
            $cnt = $c->get(Cache::key('socialcapital:mention_count:'.$profile_id));
            if (is_integer($cnt)) {
                return (int) $cnt;
            }
        }

        $rep = new Reply();
        $rep->profile_id = $profile_id;
        $rep->whereAdd(sprintf('modified > %s', strtotime("-4 months")));

        $cnt = (int) $rep->count();

        $cnt = ($cnt > 0) ? $cnt - 1 : $cnt;

        if (!empty($c)) {
            $c->set(Cache::key('socialcapital:mention_count:'.$profile_id), $cnt);
        }

        return $cnt;
    }

    function favesCount($profile_id)
    {
        $c = Cache::instance();

        if (!empty($c)) {
            $cnt = $c->get(Cache::key('socialcapital:faves_count:'.$profile_id));
            if (is_integer($cnt)) {
                return (int) $cnt;
            }
        }

        $fave = new Fave();
        $fave->joinAdd(array('notice_id', 'notice:id'));
        $fave->whereAdd(sprintf('notice.profile_id = %d', $profile_id));
        $fave->whereAdd(sprintf('fave.modified > %s', strtotime("-4 months")));
        
        $faves = $fave->count('notice_id');


        $cnt = (int) $faves;

        if (!empty($c)) {
            $c->set(Cache::key('socialcapital:faves_count:'.$profile_id), $cnt);
        }

        return $cnt;
    }

    function sharesCount($profile_id)
    {
        $c = Cache::instance();

        if (!empty($c)) {
            $cnt = $c->get(Cache::key('socialcapital:shares_count:'.$profile_id));
            if (is_integer($cnt)) {
                return (int) $cnt;
            }
        }

        $share = new Notice();
        $share->whereAdd('repeat_of IS NOT NULL');
        $share->whereAdd(sprintf('notice.profile_id = %d', $profile_id));
        $share->whereAdd(sprintf('modified > %s', strtotime("-4 months")));
        
        $shares = $share->count('id');


        $cnt = (int) $shares;

        if (!empty($c)) {
            $c->set(Cache::key('socialcapital:shares_count:'.$profile_id), $cnt);
        }

        return $cnt;
    }

    function sharedCount($profile_id)
    {
        $c = Cache::instance();

        if (!empty($c)) {
            $cnt = $c->get(Cache::key('socialcapital:shared_count:'.$profile_id));
            if (is_integer($cnt)) {
                return (int) $cnt;
            }
        }

        $shared = new Notice();
        $shared->whereAdd(sprintf('repeat_of IN (SELECT id FROM notice WHERE profile_id=%d)', $profile_id));
        $shared->whereAdd(sprintf('modified > %s', strtotime("-4 months")));

        $shares = $shared->count('id');

        $cnt = (int) $shares;

        if (!empty($c)) {
            $c->set(Cache::key('socialcapital:shared_count:'.$profile_id), $cnt);
        }

        return $cnt;
    }

    function noticesFromBlogCount($profile_id)
    {
        $c = Cache::instance();

        if (!empty($c)) {
            $cnt = $c->get(Cache::key('socialcapital:noticesfromblog_count:'.$profile_id));
            if (is_integer($cnt)) {
                return (int) $cnt;
            }
        }


        $noticesFromBlog = new Notice();
        $noticesFromBlog->profile_id = $profile_id;
        $noticesFromBlog->source = 'wpgnusocial';
        $noticesFromBlog->whereAdd(sprintf('modified > %s', strtotime("-4 months")));

        $notices = $noticesFromBlog->count('id');

        $cnt = (int) $notices;

        if (!empty($c)) {
            $c->set(Cache::key('socialcapital:noticesfromblog_count:'.$profile_id), $cnt);
        }

        return $cnt;
    }
}
