package com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by San Vo on 28/02/2018.
 */

public class OfferMessage extends InRoomMessage {
    private String _sdp;

    public OfferMessage(String roomName, String sendTo, String sdp) {
        super("offer", roomName, sendTo);
        _sdp = sdp;
    }

    public OfferMessage(JSONObject data) throws JSONException {
        super("offer", data.getString("room_name"), data.getString("send_to"));
        _from = data.getString("from");
        _sdp = data.getString("sdp");
    }

    @Override
    public JSONObject toJson() throws JSONException {
        JSONObject json = new JSONObject();
        json.put("type", "offer");
        json.put("send_to", _sendTo);
        json.put("from", _from);
        json.put("room_name", _roomName);
        json.put("sdp", _sdp);
        return json;
    }

    public String getSdp() {
        return _sdp;
    }
}
