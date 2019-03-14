package com.unirobot.webrtc.unibocom.signaling;

import com.unirobot.webrtc.unibocom.client.RoomHandler;
import com.unirobot.webrtc.unibocom.client.object.Room;
import com.unirobot.webrtc.unibocom.client.object.User;
import com.unirobot.webrtc.unibocom.signaling.callback.SignalingHandlerDelegate;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.AnswerMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.CallMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.CandidateMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.HangupMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.OfferMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.ResponseMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.RoomMessage;
import com.unirobot.webrtc.unibocom.signaling.message.Message;
import com.unirobot.webrtc.unibocom.signaling.message.MessageFactory;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.CallingFailMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.EnterFailMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.ExitFailMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.FailMessage;

import org.json.JSONException;
import org.json.JSONObject;
import org.webrtc.IceCandidate;
import org.webrtc.SessionDescription;

/**
 * Created by San Vo on 28/02/2018.
 */

public class SignalHandler {
    private SocketHandler _socketHandler;
    private RoomHandler _roomHandler;

    private SignalingHandlerDelegate _signalingHandlerDelegate;

    public SignalHandler(SignalingHandlerDelegate signalingHandlerDelegate, SocketHandler socketHandler, RoomHandler roomHandler) {
        _socketHandler = socketHandler;
        _roomHandler = roomHandler;
        _signalingHandlerDelegate = signalingHandlerDelegate;
    }

    public void handleMessage(JSONObject msg) throws JSONException {
        Message msgObj = MessageFactory.create(msg);
        if (msgObj instanceof RoomMessage) {
            RoomMessage joinMsg = (RoomMessage) msgObj;
            _roomHandler.updateMembers(joinMsg.getRoomName(), joinMsg.getMembers(), joinMsg.getLeftMember(), joinMsg.getJoinedMember());
        } else if (msgObj instanceof CallMessage) {
            CallMessage callMsg = (CallMessage) msgObj;
            Room room = _roomHandler.getRoom(callMsg.getRoomName());
            User caller = room.getMember(callMsg.getFrom());
            if (_signalingHandlerDelegate != null)
                _signalingHandlerDelegate.onBeCalled(room, caller);
        } else if (msgObj instanceof ResponseMessage) {
            ResponseMessage responseMsg = (ResponseMessage) msgObj;
            Room room = _roomHandler.getRoom(responseMsg.getRoomName());
            User acceptor = room.getMember(responseMsg.getFrom());
            if (_signalingHandlerDelegate != null)
                _signalingHandlerDelegate.onBeAccepted(room, acceptor);
        } else if (msgObj instanceof OfferMessage) {
            OfferMessage offerMsg = (OfferMessage) msgObj;
            Room room = _roomHandler.getRoom(offerMsg.getRoomName());
            User caller = room.getMember(offerMsg.getFrom());
            if (_signalingHandlerDelegate != null)
                _signalingHandlerDelegate.onReceiveOffer(room, caller, offerMsg.getSdp());
        } else if (msgObj instanceof AnswerMessage) {
            AnswerMessage answerMsg = (AnswerMessage) msgObj;
            Room room = _roomHandler.getRoom(answerMsg.getRoomName());
            User acceptor = room.getMember(answerMsg.getFrom());
            if (_signalingHandlerDelegate != null)
                _signalingHandlerDelegate.onReceiveAnswer(room, acceptor, answerMsg.getSdp());
        } else if (msgObj instanceof CandidateMessage) {
            CandidateMessage candidateMsg = (CandidateMessage) msgObj;
            Room room = _roomHandler.getRoom(candidateMsg.getRoomName());
            User acceptor = room.getMember(candidateMsg.getFrom());
            if (_signalingHandlerDelegate != null) {
                _signalingHandlerDelegate.onReceiveCandidate(room, acceptor, candidateMsg.getSdpMLineIndex(), candidateMsg.getSdpMid(), candidateMsg.getCandidate());
            }
        } else if (msgObj instanceof HangupMessage) {
            HangupMessage hangupMsg = (HangupMessage) msgObj;
            Room room = _roomHandler.getRoom(hangupMsg.getRoomName());
            if (room != null) {
                User partner = room.getMember(hangupMsg.getFrom());
                if (_signalingHandlerDelegate != null) {
                    _signalingHandlerDelegate.onReceiveHangup(room, partner);
                }
            }
        } else if (msgObj instanceof EnterFailMessage) {
            EnterFailMessage enterFailMessage = (EnterFailMessage) msgObj;
            _roomHandler.removeRoom(enterFailMessage.getRoomName());
            if (_signalingHandlerDelegate != null) {
                _signalingHandlerDelegate.onJoinRoomFail(enterFailMessage.getRoomName(), enterFailMessage.getErrorMsg());
            }
        } else if (msgObj instanceof ExitFailMessage) {
            ExitFailMessage exitFailMessage = (ExitFailMessage) msgObj;
            if (_signalingHandlerDelegate != null) {
                _signalingHandlerDelegate.onLeaveRoomFail(exitFailMessage.getRoomName(), exitFailMessage.getErrorMsg());
            }
        } else if (msgObj instanceof CallingFailMessage) {
            CallingFailMessage callingFailMessage = (CallingFailMessage) msgObj;
            if (_signalingHandlerDelegate != null && _roomHandler != null) {
                Room room = _roomHandler.getRoom(callingFailMessage.getRoomName());
                _signalingHandlerDelegate.onCallingFail(room, room.getMember(callingFailMessage.getUid()), callingFailMessage.getErrorMsg());
            }
        } else if (msgObj instanceof FailMessage) {
            FailMessage failMessage = (FailMessage) msgObj;
            if (_signalingHandlerDelegate != null) {
                _signalingHandlerDelegate.onErrorMessage(failMessage.getRoomName(), failMessage.getErrorMsg());
            }
        }
    }

    public void joinRoom(String roomName, User myUser) throws JSONException {
        if (_socketHandler.isConnected()) {
            JSONObject info = new JSONObject();
            info.put("room_name", roomName);
            info.put("nick_name", myUser.getName());
            info.put("uid", myUser.getUid());
            _socketHandler.emit("enter", info);
        }
    }

    public void leaveRoom(String roomName) {
        if (_socketHandler.isConnected()) {
            _socketHandler.emit("exit", roomName);
        }
    }

    public void call(String roomName, String targetUid) throws JSONException {
        if (_socketHandler.isConnected()) {
            CallMessage callMsg = new CallMessage(roomName, targetUid);
            _socketHandler.emit("message", callMsg.toJson());
        }
    }

    public void response(String roomName, String targetUid) throws JSONException {
        if (_socketHandler.isConnected()) {
            ResponseMessage responseMsg = new ResponseMessage(roomName, targetUid);
            _socketHandler.emit("message", responseMsg.toJson());
        }
    }

    public void sendOffer(String roomName, String targetUid, SessionDescription sdp) throws JSONException {
        if (_socketHandler.isConnected() && roomName != null && targetUid != null && sdp != null) {
            OfferMessage offerMsg = new OfferMessage(roomName, targetUid, sdp.description);
            _socketHandler.emit("message", offerMsg.toJson());
        }
    }

    public void sendAnswer(String roomName, String targetUid, SessionDescription sdp) throws JSONException {
        if (_socketHandler.isConnected() && roomName != null && targetUid != null && sdp != null) {
            AnswerMessage answerMsg = new AnswerMessage(roomName, targetUid, sdp.description);
            _socketHandler.emit("message", answerMsg.toJson());
        }
    }

    public void sendCandidate(String roomName, String targetUid, IceCandidate iceCandidate) throws JSONException {
        if (_socketHandler.isConnected() && roomName != null && targetUid != null && iceCandidate != null) {
            CandidateMessage candidateMsg = new CandidateMessage(roomName, targetUid, iceCandidate.sdpMLineIndex, iceCandidate.sdpMid, iceCandidate.sdp);
            _socketHandler.emit("message", candidateMsg.toJson());
        }
    }

    public void hangup(String roomName, String targetUid) throws JSONException {
        if (_socketHandler.isConnected()) {
            HangupMessage hangupMsg = new HangupMessage(roomName, targetUid);
            _socketHandler.emit("message", hangupMsg.toJson());
        }
    }
}