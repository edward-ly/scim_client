<?php

declare(strict_types=1);

namespace OCA\ScimClient\Service;

use OCA\ScimClient\AppInfo\Application;
use OCA\ScimClient\Db\ScimServer;
use OCA\ScimClient\Db\ScimServerMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\Exception;
use OCP\Security\ICrypto;
use Psr\Log\LoggerInterface;

class ScimServerService {

	public function __construct(
		private readonly LoggerInterface $logger,
		private readonly ScimServerMapper $mapper,
		private readonly ICrypto $crypto,
	) {
	}

	public function registerScimServer(array $params): ?ScimServer {
		if (!mb_strlen($params['name'])) {
			$this->logger->error('Failed to register SCIM server. Name cannot be empty.');
			return null;
		}

		if (!str_starts_with($params['url'], 'http://') && !str_starts_with($params['url'], 'https://')) {
			$this->logger->error('Failed to register SCIM server. URL must start with `http://` or `https://`.');
			return null;
		}

		$params['url'] = rtrim($params['url'], '/');

		if (!empty($params['api_key'])) {
			$params['api_key'] = $this->crypto->encrypt($params['api_key']);
		}

		try {
			return $this->mapper->insert(new ScimServer($params));
		} catch (Exception $e) {
			$this->logger->error('Failed to register SCIM server. Error: ' . $e->getMessage(), ['exception' => $e]);
			return null;
		}
	}

	public function unregisterScimServer(ScimServer $server): ?ScimServer {
		try {
			return $this->mapper->delete($server);
		} catch (Exception $e) {
			$this->logger->error('Failed to unregister SCIM server. Error: ' . $e->getMessage(), ['exception' => $e]);
			return null;
		}
	}

	public function getRegisteredScimServers(): array {
		try {
			return array_map(static function (ScimServer $s): array {
				$server = $s->jsonSerialize();

				// Mask API key with dummy secret if set
				if (!empty($server['api_key'])) {
					$server['api_key'] = Application::DUMMY_SECRET;
				}

				return $server;
			}, $this->mapper->findAll());
		} catch (Exception $e) {
			$this->logger->debug('Failed to get registered SCIM servers. Error: ' . $e->getMessage(), ['exception' => $e]);
			return [];
		}
	}

	public function getScimServer(int $id): ?ScimServer {
		try {
			return $this->mapper->findById($id);
		} catch (DoesNotExistException|MultipleObjectsReturnedException|Exception $e) {
			$this->logger->debug('Failed to get SCIM server. Error: ' . $e->getMessage(), ['exception' => $e]);
			return null;
		}
	}

	public function getScimServerByName(string $name): ?ScimServer {
		try {
			return $this->mapper->findByName($name);
		} catch (DoesNotExistException|MultipleObjectsReturnedException|Exception $e) {
			$this->logger->debug('Failed to get SCIM server by name. Error: ' . $e->getMessage(), ['exception' => $e]);
			return null;
		}
	}

	public function updateScimServer(ScimServer $server): ?ScimServer {
		try {
			return $this->mapper->update($server);
		} catch (Exception $e) {
			$this->logger->error('Failed to update ScimServer. Error: ' . $e->getMessage(), ['exception' => $e]);
			return null;
		}
	}
}