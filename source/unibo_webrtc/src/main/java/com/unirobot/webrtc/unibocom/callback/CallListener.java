package com.unirobot.webrtc.unibocom.callback;

import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface CallListener {
    //IUnibo - call
    void onCallSuccess(Room room, User partner);

    void onCallFail(Room room, User partner, String msg);

    void onHangupSuccess(Room room, User partner);

    void onHangupFail(Room room, User partner);

    void onBeCalled(Room room, User caller);

    void onBeAccepted(Room room, User acceptor);

    void onHungup(Room room, User partner);

    void setRemoteProxyFail(String message);
}
