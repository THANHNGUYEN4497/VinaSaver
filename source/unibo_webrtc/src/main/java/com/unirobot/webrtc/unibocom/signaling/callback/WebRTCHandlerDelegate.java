package com.unirobot.webrtc.unibocom.signaling.callback;

import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface WebRTCHandlerDelegate {
    void onWebRTCHandlerError(String errorMsg);
    void onIceConnected(Room room, User partner);
    void onIceClosed(Room room, User partner);
}
