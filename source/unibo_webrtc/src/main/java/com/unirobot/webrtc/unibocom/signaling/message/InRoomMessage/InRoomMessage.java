package com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage;

import com.unirobot.webrtc.unibocom.signaling.message.Message;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by HungPhan on 20/03/2018.
 * Copyright © Saver Corp 2018.
 */

public abstract class InRoomMessage extends Message {
    protected String _sendTo;
    protected String _from;
    protected String _roomName;

    public InRoomMessage(String type, String roomName, String sendTo) {
        super(type);
        _sendTo = sendTo;
        _roomName = roomName;
    }

    public String getFrom() {
        return _from;
    }

    public String getRoomName() {
        return _roomName;
    }

    abstract JSONObject toJson() throws JSONException;
}
