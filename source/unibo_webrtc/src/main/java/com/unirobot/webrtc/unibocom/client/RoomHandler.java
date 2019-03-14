package com.unirobot.webrtc.unibocom.client;

import com.unirobot.webrtc.unibocom.callback.RoomListener;
import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;

import java.util.HashMap;
import java.util.List;

/**
 * Created by San Vo on 28/02/2018.
 */

public class RoomHandler {
    private HashMap<String, Room> _rooms;
    private RoomListener _roomDelegate;

    private HashMap<String, Room> _waitNotifyJoinedRooms;

    public RoomHandler() {
        _rooms = new HashMap<>();
        _waitNotifyJoinedRooms = new HashMap<>();
    }

    public void setRoomDelegate(RoomListener roomDelegate) {
        _roomDelegate = roomDelegate;
    }

    public Room getRoom(String roomName) {
        return _rooms.get(roomName);
    }

    public boolean createRoom(String roomName, User myUser) {
        if (_rooms.containsKey(roomName)) {
            if (_waitNotifyJoinedRooms.containsKey(roomName)) {
                _rooms.get(roomName).setMyUser(myUser);
                return true;
            } else {
                return false;
            }
        } else {
            Room room = new Room(roomName);
            room.setMyUser(myUser);

            _rooms.put(roomName, room);
            _waitNotifyJoinedRooms.put(roomName, room);

            return true;
        }
    }

    public void removeRoom(String roomName) {
        _rooms.remove(roomName);
        _waitNotifyJoinedRooms.remove(roomName);
    }

    public void updateMembers(String roomName, List<User> members, User leftMember, User joinedMember) {
        Room room = _rooms.get(roomName);
        // Left member
        if (leftMember != null) {
            if (room.getMyUser().equals(leftMember)) {
                _roomDelegate.onLeaveRoomSuccess(roomName);
                removeRoom(roomName);
                return;
            } else {
                room.removeMember(leftMember);
            }
        }
        // Join member
        if (joinedMember != null) {
            if (room.getMyUser().equals(joinedMember)) {
                if (_waitNotifyJoinedRooms.containsKey(room.getRoomName())) {
                    _waitNotifyJoinedRooms.remove(room.getRoomName());
                    _roomDelegate.onJoinRoomSuccess(room);

                    // The first time join room
                    for (User user : members) {
                        if (room.getMyUser().equals(user)) {
                            continue;
                        }
                        room.addMember(user);
                    }
                }
            } else {
                room.addMember(joinedMember);
            }
        }
        if (leftMember != null || joinedMember != null) {
            _roomDelegate.onRoomInfoChanged(room);
        }
    }

    public void clearAll() {
        _rooms.clear();
        _waitNotifyJoinedRooms.clear();
    }
}
