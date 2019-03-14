package com.unirobot.webrtc.unibocom.callback;

import com.unirobot.webrtc.unibocom.client.object.Room;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface RoomListener {
    //IUnibo - createRoom
    void onJoinRoomSuccess(Room room);

    void onJoinRoomFail(String roomName, String msg);

    //IUnibo - leaveRoom
    void onLeaveRoomSuccess(String roomName);

    void onLeaveRoomFail(String roomName, String msg);

    //
    void onRoomInfoChanged(Room room);
}
