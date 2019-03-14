package com.unirobot.webrtc.unibocom.callback;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface ConnectionListener {
    void onConnectSuccess();
    void onConnectFail(String errorMsg);
    void onDisconnected();
    void onDisconnectFail(String errorMsg);
}
