package com.unirobot.webrtc.unibocom;

import android.content.Context;

import com.unirobot.webrtc.unibocom.callback.CallListener;
import com.unirobot.webrtc.unibocom.callback.ConnectionListener;
import com.unirobot.webrtc.unibocom.callback.EventListener;
import com.unirobot.webrtc.unibocom.callback.RoomListener;
import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;
import com.unirobot.webrtc.unibocom.configuration.StreamConfiguration;
import com.unirobot.webrtc.unibocom.control.UniboProxyRenderer;
import com.unirobot.webrtc.unibocom.control.UniboVideoView;

import java.util.List;

/**
 * Created by San Vo on 27/02/2018.
 */

public interface IUnibo {
    void configure(String signalingUrl, List<String> stunUrls, StreamConfiguration streamConfiguration);

    void prepare(Context context);

    void configureVideoView(UniboVideoView videoView, UniboClient.RenderScale renderScale, boolean isOnTop);

    void startLocalMediaSource();

    void stopLocalMediaSource();

    void setLocalRenderProxy(UniboProxyRenderer localRenderProxy);

    void setRemoteRenderProxy(Room room, User targetUser, UniboProxyRenderer localRenderProxy);

    boolean isConnected();

    void release();

    //-----------

    void connect();

    void disconnect();

    void joinRoom(String roomName, String nickName, String uid);

    void leaveRoom(String roomName);

    void call(Room room, User otherUser);

    void response(Room room, User caller);

    void hangup(Room room, User partner);

    void hangupAll();

    //-----------

    void setAudioEnabled(boolean enable);

    void setVideoEnabled(boolean enable);

    void switchCamera();

    //-----------

    void setConnectionDelegate(ConnectionListener delegate);

    void setEventDelegate(EventListener delegate);

    void setCallDelegate(CallListener delegate);

    void setRoomDelegate(RoomListener delegate);
}
