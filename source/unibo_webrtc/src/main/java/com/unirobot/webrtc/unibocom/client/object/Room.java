package com.unirobot.webrtc.unibocom.client.object;

import android.support.annotation.NonNull;

import java.util.ArrayList;
import java.util.List;

/**
 * Created by San Vo on 28/02/2018.
 */

public class Room implements Comparable<Room> {
    private String _roomName;
    private User _myUser;
    private ArrayList<User> _otherUser;

    public Room(String roomName) {
        _roomName = roomName;
    }

    public String getRoomName() {
        return _roomName;
    }

    public void setMyUser(User myUser) {
        _myUser = myUser;
        _otherUser = new ArrayList<>();
    }

    public User getMyUser() {
        return _myUser;
    }

    public List<User> getMembers() {
        return _otherUser;
    }

    public User getMember(String uid) {
        for (User u : _otherUser) {
            if (u.getUid().equals(uid))
                return u;
        }
        return null;
    }

    public synchronized void addMember(User member) {
        if (!_otherUser.contains(member)) {
            _otherUser.add(member);
        }
    }

    public void removeMember(User leftMember) {
        if (_otherUser.contains(leftMember)) {
            _otherUser.remove(leftMember);
        }
    }

    @Override
    public int compareTo(@NonNull Room room) {
        return _roomName.compareTo(room._roomName);
    }
}
