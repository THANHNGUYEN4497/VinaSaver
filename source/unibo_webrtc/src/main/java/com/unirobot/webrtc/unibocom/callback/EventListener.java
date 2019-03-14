package com.unirobot.webrtc.unibocom.callback;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface EventListener {
    void onEventError(String errorMsg);

    void onRoomEventError(String roomName, String errorMsg);
}
