<?php

//Connect someone (person) to your network
//2 cases : the person exists in the db or he has to be invited
class ConnectAction extends CTKAction {
    public function run($id = null,$type = null, $ownerLink = null, $targetLink = null) {

    	$invitedUserId = "";

        if (! $this->userLogguedAndValid()) {
        	Rest::json(array("result" => false, "msg" => "The current user is not valid : please login."));
        }

        //Case spécial : Vérifie si l'email existe et retourne l'id de l'utilisateur
        if (!empty($_POST["invitedUserEmail"]))
        	$invitedUserId = Person::getPersonIdByEmail($_POST["invitedUserEmail"]) ;

        //Case 1 : the person invited exists in the db
        if (!empty($_POST["connectUserId"]) || !empty($invitedUserId)) {
        	if (!empty($_POST["connectUserId"]))
        		$invitedUserId = $_POST["connectUserId"];
        	$res = Link::connect($this->currentUserId, Person::COLLECTION, $invitedUserId, Person::COLLECTION, $this->currentUserId, Link::person2person);
            $actionType = ActStr::VERB_FOLLOW;
		//Case 2 : the person invited does not exist in the db
		} else if (empty($_POST["invitedUserId"])) {
			if(empty($targetLink)){
				$newPerson = array("name" => $_POST["invitedUserName"], "email" => $_POST["invitedUserEmail"], "invitedBy" => $this->currentUserId);
				if(!empty($_POST["msgEmail"]))
					$res = Person::createAndInvite($newPerson, $_POST["msgEmail"]);
				else
					$res = Person::createAndInvite($newPerson);

	            $actionType = ActStr::VERB_INVITE;
	            if ($res["result"]) {
	                $invitedUserId = $res["id"];
	                $res = Link::connect($this->currentUserId, Person::COLLECTION, $invitedUserId, Person::COLLECTION, $this->currentUserId, Link::person2person);
	            }
	        }
	        else{
		        $actionType = ActStr::VERB_JOIN;
		        $invitedUserId = Yii::app()->session['userId'];
		        $res = Link::connectPerson(Yii::app()->session['userId'], Person::COLLECTION, $id, $type, $ownerLink,$targetLink);

			}
		}
		
		Notification::connectPeople ( $invitedUserId, $this->currentUserId , Yii::app()->session['user']["name"], $actionType ) ;

        if (@$res["result"] == true) {
            $person = Person::getSimpleUserById($invitedUserId);
            $res = array("result" => true, "invitedUser" => $person);
        } else {
            $res = array("result" => false, "msg" => $res["msg"]);
        }

		Rest::json($res);
    }
}
