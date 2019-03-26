<?php

/*
 * This file is part of a XenForo add-on.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jrahmy\StickyThreadSort\XF\Pub\Controller;

use XF\Mvc\ParameterBag;

/**
 * Extends \XF\Pub\Controller\Forum
 */
class Forum extends XFCP_Forum
{
    /**
     * @param ParameterBag $params
     *
     * @return \XF\Mvc\Reply\AbstractReply
     */
    public function actionForum(ParameterBag $params)
    {
        $reply = parent::actionForum($params);

        if ($reply instanceof \XF\Mvc\Reply\View) {
            $options = $this->options()->jSts;
            $order = $options['order'];
            $direction = $options['direction'];

            $forum = $reply->getParam('forum');
            $sorts = $this->getAvailableForumSorts($forum);

            if ($order && isset($sorts[$order])) {
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

                /** @var \XF\Mvc\Entity\AbstractCollection $stickyThreads */
                $stickyThreads = $reply->getParam('stickyThreads')
                    ?: $this->em()->getEmptyCollection();
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
