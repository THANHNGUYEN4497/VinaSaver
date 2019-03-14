package com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by San Vo on 28/02/2018.
 */

public class CandidateMessage extends InRoomMessage {
    private int _sdpMLineIndex;
    private String _sdpMid;
    private String _candidate;

    public CandidateMessage(String roomName, String sendTo, int sdpMLineIndex, String sdpMid, String candidate) {
        super("candidate", roomName, sendTo);
        _sdpMLineIndex = sdpMLineIndex;
        _sdpMid = sdpMid;
        _candidate = candidate;
    }

    public CandidateMessage(JSONObject data) throws JSONException {
        super("candidate", data.getString("room_name"), data.getString("send_to"));
        _from = data.getString("from");
        _sdpMLineIndex = data.getInt("sdp_m_line_index");
        _sdpMid = data.getString("sdp_mid");
        _candidate = data.getString("candidate");
    }

    @Override
    public JSONObject toJson() throws JSONException {
        JSONObject json = new JSONObject();
        json.put("type", "candidate");
        json.put("send_to", _sendTo);
        json.put("from", _from);
        json.put("room_name", _roomName);
        json.put("sdp_m_line_index", _sdpMLineIndex);
        json.put("sdp_mid", _sdpMid);
        json.put("candidate", _candidate);

        return json;
    }

    public int getSdpMLineIndex() {
        return _sdpMLineIndex;
    }

    public String getSdpMid() {
        return _sdpMid;
    }

    public String getCandidate() {
        return _candidate;
    }
}
