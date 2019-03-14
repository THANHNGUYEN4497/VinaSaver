package com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage;

import com.unirobot.webrtc.unibocom.client.object.User;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by San Vo on 28/02/2018.
 */

public class RoomMessage extends InRoomMessage {
    private List<User> _members;
    private User _leftMember;
    private User _joinedMember;

    public RoomMessage(JSONObject data) throws JSONException {
        super("room", data.getString("room_name"), null);
        _members = new ArrayList<>();

        JSONArray members = (JSONArray) data.get("members");
        for (int i = 0; i < members.length(); i++) {
            _members.add(new User(members.getJSONObject(i)));
        }
        if (data.has("left_member")) {
            JSONObject leftMemberObject = data.getJSONObject("left_member");
            _leftMember = new User(leftMemberObject);
        } else {
            _leftMember = null;
        }
        if (data.has("joined_member")) {
            JSONObject joinedMemberObject = data.getJSONObject("joined_member");
            _joinedMember = new User(joinedMemberObject);
        } else {
            _joinedMember = null;
        }
    }

    public User getLeftMember() {
        return _leftMember;
    }

    public User getJoinedMember() {
        return _joinedMember;
    }

    public List<User> getMembers() {
        return _members;
    }

    @Override
    public JSONObject toJson() throws JSONException {
        //nothing
        return null;
    }


}
