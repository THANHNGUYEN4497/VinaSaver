package com.unirobot.webrtc.unibocom.client.object;

import android.support.annotation.NonNull;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by San Vo on 28/02/2018.
 */

public class User implements Comparable<User> {
    private String _socketId;
    private String _name;
    private String _uid;

    public User(String name, String socketId, String uid) {
        _name = name;
        _socketId = socketId;
        _uid = uid;
    }

    public User(JSONObject data) throws JSONException {
        _name = data.getString("nick_name");
        _socketId = data.getString("socket_id");
        _uid = data.getString("uid");
    }

    public String getSocketId() {
        return _socketId;
    }

    public String getName() {
        return _name;
    }

    public void setName(String name) {
        _name = name;
    }

    public String getUid() {
        return _uid;
    }

    @Override
    public int compareTo(@NonNull User o) {
        return _uid.compareTo(o._uid);
    }

    @Override
    public boolean equals(Object obj) {
        if (obj == null || _uid == null || !(obj instanceof User)) return false;

        User u = (User) obj;
        return _uid.equals(u._uid);
    }
}
