<?php
class ListAction extends CAction
{
    public function run( $tpl=null )
    {
    	$controller = $this->getController();
        $res = array( "result" => false , "msg" => Yii::t("common","Something went wrong!") );
        if( !Person::logguedAndValid() )
            return array("result"=>false, "msg"=>Yii::t("common","Please Login First") );
        else{	
			try {
				$res = Favorite::get(@$_POST['id'], @$_POST['type']);
			} catch (CTKException $e) {
				$res = array( "result" => false , "msg" => $e->getMessage() );
			}
		}

		if(Yii::app()->request->isAjaxRequest && @$tpl) {
			if( $res["list"]["citoyens"] )
				$res["list"]["person"] = $res["list"]["citoyens"];
			$res["type"] = "favorites";
        	echo $controller->renderPartial("../default/".$tpl ,$res["list"] ,true );
		} else
			return Rest::json($res);
    }
}