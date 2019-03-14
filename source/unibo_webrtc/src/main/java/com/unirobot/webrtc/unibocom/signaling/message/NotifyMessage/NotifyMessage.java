package com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage;

import com.unirobot.webrtc.unibocom.signaling.message.Message;

/**
 * Created by HungPhan on 20/03/2018.
 * Copyright © Saver Corp 2018.
 */

public abstract class NotifyMessage extends Message {
    protected String _event;

    public NotifyMessage(String type, String event) {
        super(type);
        _event = event;
    }

    public String getEvent() {
        return _event;
    }
}
