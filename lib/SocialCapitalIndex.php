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

if (!defined('GNUSOCIAL') || !defined('STATUSNET')) {
    // This check helps protect against security problems;
    // your code file can't be executed directly from the web.
    exit(1);
}

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

    /**
     * TODO: Document
     */
    static function init($user_id)
    {
        $sc = new SocialCapitalIndex();
        $sc->user_id = $user_id;
        $sc->ttl_notices = 0;
        $sc->ttl_followers = 0;
        $sc->ttl_following = 0;

        $sc->ttl_mentions = 0; //replies
        $sc->ttl_faved = 0;
        $sc->ttl_shares = 0;
        $sc->ttl_shared = 0;

        $sc->ttl_noticesFromBlog = 0;



        $user = User::getKV('id', $user_id);

        if (!empty($user)) {

        $profile = $user->getProfile();

        $sc->ttl_notices = 0;
        $sc->ttl_replies = 0;
        $sc->ttl_bookmarks = 0;

        // Gather "Notice" information from db and place into appropriate arrays
        $sc->ttl_notices = $profile->noticeCount();

        // $notices = self::cachedQuery('Notice', sprintf("SELECT * FROM notice
        //     WHERE profile_id = %d",
        //     $user_id));
        //
        //
        //
        // $sc->ttl_notices = count($notices->_items);

            /*
        foreach($notices->_items as $notice) {


            $date_created->modify($notice->created); // String to Date

            // Extract group info
            $groups = $notice->getGroups();
            foreach ($groups as $group) {
                $sa->graphs['notices_per_group'][$group->nickname]['notices'][] = $notice;
            }

            // Extract hashtag info
            $hashtags = $notice->getTags();
            foreach($hashtags as $hashtag) {
                $sa->graphs['notices_per_hashtag'][$hashtag]['notices'][] = $notice;
            }

            // Repeats
            if($notice->repeat_of) {
                $repeat = Notice::getKV('id', $notice->repeat_of);
                $u_repeat = Profile::getKV('id', $repeat->profile_id);

                if(!isset($sa->graphs['people_you_repeated'][$u_repeat->nickname])) {
                    $sa->graphs['people_you_repeated'][$u_repeat->nickname] = array('notices' => array());
                }
                $sa->graphs['people_you_repeated'][$u_repeat->nickname]['notices'][] = $notice;
                $sa->graphs['trends'][$date_created->format('Y-m-d')]['repeats'][] = $notice;
            }

            // Clients
            if(!isset($sa->graphs['clients'][$notice->source])) {
                $sa->graphs['clients'][$notice->source] = array('clients' => array());
            }
            $sa->graphs['clients'][$notice->source]['clients'][] = $notice;

            // Replies
            if($notice->reply_to) {
                $reply_to = Notice::getKV('id', $notice->reply_to);
                $repliee = Profile::getKV('id', $reply_to->profile_id);

                if(!isset($sa->graphs['people_you_replied_to'][$repliee->nickname])) {
                    $sa->graphs['people_you_replied_to'][$repliee->nickname] = array('notices' => array());
                }
                $sa->graphs['people_you_replied_to'][$repliee->nickname]['notices'][] = $notice;

                $sa->ttl_replies++;
            }

            // Bookmarks
            if(preg_match('/\/bookmark$/', $notice->object_type)) {
                $sa->graphs['trends'][$date_created->format('Y-m-d')]['bookmarks'][] = $notice;
                $sa->ttl_bookmarks++;
            }

            // Notices
            $sa->graphs['trends'][$date_created->format('Y-m-d')]['notices'][] = $notice;


            $sc->ttl_notices++; // FIXME: Do we want to include bookmarks with notices now that we have a 'bookmarks' trend?
        }

        */


        // Favored notices (both by 'you' and 'others')
        //$sc->ttl_faves = 0;
        //$sc->ttl_o_faved = 0;
        //$faved = self::cachedQuery('Fave', sprintf("SELECT * FROM fave"));

        /*
        foreach($faved->_items as $fave) {

            $notice = Notice::getKV('id', $fave->notice_id);

            // User's faves
            if($notice->profile_id == $user_id) {
                $sc->ttl_o_faved++;
            }
        }
        */

        // People who mentioned you
        $sc->ttl_mentions = SocialCapitalIndex::mentionsCount($profile->id);

        $sc->ttl_faved = SocialCapitalIndex::favesCount($profile->id);

        $sc->ttl_shares = SocialCapitalIndex::sharesCount($profile->id);

        $sc->ttl_shared = SocialCapitalIndex::sharedCount($profile->id);

        $sc->ttl_noticesFromBlog = SocialCapitalIndex::noticesFromBlogCount($profile->id);

        /*
        foreach($mentions[$user_id] as $mention) {

                $sc->ttl_mentions++;
        }
        */

        // Hosts you are following
        $sc->ttl_following = $profile->subscriptionCount();
        // $sc->ttl_following = 0;
        // $arr_following = self::listGetClass('Subscription', 'subscriber', array($user_id));
        //
        // $sc->ttl_following = count($arr_following[$user_id]);
        /*
        foreach($arr_following[$user_id] as $following) {
            // This is in my DB, but doesn't show up in my 'Following' total (???)
            if($following->subscriber == $following->subscribed) {
                continue;
            }
            $sc->ttl_following++;
        }
        */

        // Hosts who follow you
        $sc->ttl_followers = $profile->subscriberCount();
        // $sc->ttl_followers = 0;
        // $followers = self::listGetClass('Subscription', 'subscribed', array($user_id));
        //
        // $sc->ttl_followers = count($followers[$user_id]);
        /*
        foreach($followers[$user_id] as $follower) {
            // This is in my DB, but doesn't show up in my 'Following' total (???)
            if($follower->subscriber == $follower->subscribed) {
                continue;
            }

            $sc->ttl_followers++;
        }
        */
        }

        return $sc;    
    }

    function index()
    {
        $emision = ($this->ttl_notices + $this->ttl_shares) * 2;
        $adhesion = ($this->ttl_followers + $this->ttl_faved + $this->ttl_shared) * 5;
        $participacion = ($this->ttl_mentions + $this->ttl_following) * 8;
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

        $notices = $noticesFromBlog->count('id');

        $cnt = (int) $notices;

        if (!empty($c)) {
            $c->set(Cache::key('socialcapital:noticesfromblog_count:'.$profile_id), $cnt);
        }

        return $cnt;
    }
}
