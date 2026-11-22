<?php
//载入微信模板...
function _get_wx_tpl_id($type) {
    $wx_tpl_string = "";
    if ($type == "msg") {
        //通用消息模板...
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "keyword1":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "keyword2":{
                        "value":"{keyword2.DATA}",
                        "color":"#000000"
                    },
                    "keyword3":{
                        "value":"{keyword3.DATA}",
                        "color":"#000000"
                    },
                    "keyword4":{
                        "value":"{keyword4.DATA}",
                        "color":"#000000"
                    },
                    "remark":{
                        "value":"{remark.DATA}",
                        "color":"#ff0000"
                    }
                }
            }';
    }
    elseif ($type == "msg2") {
        //通用消息模板2... 9.02.14
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "keyword1":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "keyword2":{
                        "value":"{keyword3.DATA}",
                        "color":"#000000"
                    },
                    "remark":{
                        "value":"{remark.DATA}",
                        "color":"#ff0000"
                    }
                }
            }';
    }



    elseif ($type == "reguser") {
        //新会员加入...
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "keyword1":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "keyword2":{
                        "value":"{keyword2.DATA}",
                        "color":"#000000"
                    },
                    "remark":{
                        "value":"{remark.DATA}",
                        "color":"#ff0000"
                    }
                }
            }';
    }
    elseif ($type == "vip") {
        //开通VIP
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "keyword1":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "keyword2":{
                        "value":"{keyword2.DATA}",
                        "color":"#000000"
                    },
                    "keyword3":{
                        "value":"{keyword3.DATA}",
                        "color":"#000000"
                    },
                    "remark":{
                        "value":"{remark.DATA}",
                        "color":"#ff0000"
                    }
                }
            }';
    }
    elseif ($type == "orderpay") {
        //订单支付(会员)..
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "orderMoneySum":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "orderProductName":{
                        "value":"{keyword2.DATA}",
                        "color":"#000000"
                    },
                    "Remark":{
                        "value":"{remark.DATA}",
                        "color":"#000000"
                    }
                }
            }';
    }

    elseif ($type == "auditok") {
        //审核通过
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "keyword1":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "keyword2":{
                        "value":"{keyword2.DATA}",
                        "color":"#000000"
                    },
                    "remark":{
                        "value":"{remark.DATA}",
                        "color":"#ff0000"
                    }
                }
            }';
    }
    elseif ($type == "auditfail") {
        //审核失败
        $wx_tpl_string = '{
            "touser":"{openid}",
            "template_id":"{template_id}",
            "url":"{url}",
            "topcolor":"#FF0000",
                "data":{
                    "first":{
                        "value":"{first.DATA}",
                        "color":"#000000"
                    },
                    "keyword1":{
                        "value":"{keyword1.DATA}",
                        "color":"#000000"
                    },
                    "keyword2":{
                        "value":"{keyword2.DATA}",
                        "color":"#000000"
                    },
                    "keyword3":{
                        "value":"{keyword3.DATA}",
                        "color":"#000000"
                    },
                    "remark":{
                        "value":"{remark.DATA}",
                        "color":"#ff0000"
                    }
                }
            }';
    }

    return $wx_tpl_string;
}
?>