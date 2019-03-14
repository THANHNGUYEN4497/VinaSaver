package com.unirobot.webrtc.unibocom.signaling.callback;

import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface SignalingHandlerDelegate {
    void onBeCalled(Room room, User caller);

    void onBeAccepted(Room room, User acceptor);

    void onCallingFail(Room room, User partner, String msg);

    void onReceiveOffer(Room room, User caller, String sdp);

    void onReceiveAnswer(Room room, User acceptor, String sdp);

    void onReceiveCandidate(Room room, User acceptor, int sdpMLineIndex, String sdpMid, String candidate);

    void onReceiveHangup(Room room, User partner);

    void onJoinRoomFail(String roomName, String msg);

    void onLeaveRoomFail(String roomName, String msg);

    void onErrorMessage(String roomName, String msg);
}
