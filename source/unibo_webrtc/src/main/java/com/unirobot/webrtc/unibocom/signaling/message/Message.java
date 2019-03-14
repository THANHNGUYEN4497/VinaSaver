package com.unirobot.webrtc.unibocom.signaling.message;

/**
 * Created by San Vo on 28/02/2018.
 */

public abstract class Message {
    protected String _type;

    public Message(String type) {
        _type = type;
    }

    public String getType() {
        return _type;
    }
}
