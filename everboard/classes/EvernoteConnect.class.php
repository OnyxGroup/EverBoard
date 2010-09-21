<?php

class EvernoteConnect
{

	public $notebooks;
	public $noteStore;
	public $authToken;
	public $user;

	public function __construct()
	{
		try{
			$userStoreTrans = new THttpClient(USER_STORE_HOST, USER_STORE_PORT, USER_STORE_URL, USER_STORE_PROTO);
		}
		catch(TTransportException $e)
		{
			print $e->errorCode.' Message:'.$e->parameter;
		}

		$userStoreProt = new TBinaryProtocol($userStoreTrans);
		$userStoreClient = new UserStoreClient($userStoreProt, $userStoreProt);

		//Version Check
		$versionOk = $userStoreClient->checkVersion("EverBoard", $GLOBALS['UserStore_CONSTANTS']['EDAM_VERSION_MAJOR'], $GLOBALS['UserStore_CONSTANTS']['EDAM_VERSION_MINOR']);

		if (!$versionOk) {
			print "Incomatible EDAM client protocol version";
			exit(1);
		}

		try{
			$authResult = $userStoreClient->authenticate(USERNAME, PASSWORD, CONSUMER_KEY, CONSUMER_SECRET);
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		}

		$this->user = $authResult->user;
		$this->authToken = $authResult->authenticationToken;

		try {
			$noteStoreTrans = new THttpClient(NOTE_STORE_HOST, NOTE_STORE_PORT, NOTE_STORE_URL . $this->user->shardId, NOTE_STORE_PROTO);
			$noteStoreProt = new TBinaryProtocol($noteStoreTrans);
			$this->noteStore = new NoteStoreClient($noteStoreProt, $noteStoreProt);
			$this->notebooks = $this->noteStore->listNotebooks($this->authToken);

		} catch (edam_error_EDAMSystemException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMUserException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (edam_error_EDAMNotFoundException $e) {
			if (isset(edam_error_EDAMErrorCode::$__names[$e->errorCode])) {
				print edam_error_EDAMErrorCode::$__names[$e->errorCode] . ": " . $e->parameter . "\n";
			} else {
				print $e->getCode() . ": " . $e->getMessage() . "\n";
			}
		} catch (Exception $e) {
			print $e->getMessage();
		}
	}
}