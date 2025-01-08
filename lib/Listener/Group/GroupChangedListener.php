<?php

declare(strict_types=1);

namespace OCA\ScimClient\Listener\Group;

use OCA\ScimClient\Service\ScimEventService;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Group\Events\GroupChangedEvent;

/**
 * @template-implements IEventListener<Event>
 */
class GroupChangedListener implements IEventListener {

	public function __construct(
		private readonly ScimEventService $scimEventService,
	) {
	}

	public function handle(Event $event): void {
		if (!($event instanceof GroupChangedEvent)) {
			return;
		}

		$params = [
			'event' => 'GroupChangedEvent',
			'group_id' => $event->getGroup()->getGID(),
			'feature' => $event->getFeature(),
			'value' => $event->getValue(),
		];

		$this->scimEventService->addScimEvent($params);
	}
}
