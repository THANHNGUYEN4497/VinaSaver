package com.unirobot.webrtc.unibocom.signaling.callback;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface SocketHandlerDelegate {
    void onReceiveSocketMessage(Object msg);
    void onWebsocketError(String errMsg);
}
