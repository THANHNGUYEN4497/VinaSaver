package com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by HungPhan on 20/03/2018.
 * Copyright © Saver Corp 2018.
 */

public class ExitFailMessage extends NotifyMessage {
    private String _roomName;
    private String _errorMsg;

    public ExitFailMessage(JSONObject data) throws JSONException {
        super("notify", data.getString("event"));
        JSONObject content = data.getJSONObject("content");
        _roomName = content.getString("room_name");
        _errorMsg = content.getString("error_msg");
    }

    public String getRoomName() {
        return _roomName;
    }

    public String getErrorMsg() {
        return _errorMsg;
    }
}
