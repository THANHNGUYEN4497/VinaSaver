package com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by San Vo on 28/02/2018.
 */

public class CallMessage extends InRoomMessage {

    public CallMessage(String roomName, String sendTo) {
        super("call", roomName, sendTo);
    }

    public CallMessage(JSONObject data) throws JSONException {
        super("call", data.getString("room_name"), data.getString("send_to"));
        _from = data.getString("from");
    }

    @Override
    public JSONObject toJson() throws JSONException {
        JSONObject json = new JSONObject();
        json.put("type", "call");
        json.put("send_to", _sendTo);
        json.put("from", _from);
        json.put("room_name", _roomName);

        return json;
    }
}
