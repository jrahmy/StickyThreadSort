<?php

/*
 * This file is part of a XenForo add-on.
 *
 * (c) Jeremy P <https://xenforo.com/community/members/jeremy-p.450/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\StickyThreadSort\XF\Pub\Controller;

use XF\Mvc\Entity\ArrayCollection;
use XF\Mvc\ParameterBag;

/**
 * Extends \XF\Pub\Controller\Forum
 *
 * @author Jeremy P <https://xenforo.com/community/members/jeremy-p.450/>
 */
class Forum extends XFCP_Forum
{
    /**
     * Sorts sticky threads in the selected order.
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionForum(ParameterBag $params)
    {
        $reply = parent::actionForum($params);

        if ($reply instanceof \XF\Mvc\Reply\View) {
            $options = $this->options()->j_sts;
            $order = $options['order'];
            $direction = $options['direction'];

            $forum = $reply->getParam('forum');
            $sorts = $this->getAvailableForumSorts($forum);

            if ($order and isset($sorts[$order])) {
                if (!in_array($direction, ['asc', 'desc'])) {
                    $direction = 'desc';
                }

                $cmpFn = null;
                if ($direction == 'desc') {
                    $cmpFn = function ($v1, $v2) {
                        if ($v1 == $v2) {
                            return 0;
                        }

                        return ($v1 < $v2) ? 1 : -1;
                    };
                }

                $stickyThreads = $reply->getParam('stickyThreads');
                $stickyThreads = $stickyThreads->toArray();

                $stickyThreads = \XF\Util\Arr::columnSort(
                    $stickyThreads,
                    $order,
                    $cmpFn
                );

                $stickyThreads = $this->em()->getBasicCollection($stickyThreads);
                $reply->setParam('stickyThreads', $stickyThreads);
            }
        }

        return $reply;
    }
}
