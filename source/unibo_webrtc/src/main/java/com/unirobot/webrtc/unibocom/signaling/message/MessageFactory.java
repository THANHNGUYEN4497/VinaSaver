package com.unirobot.webrtc.unibocom.signaling.message;

import android.util.Log;

import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.AnswerMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.CallMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.CandidateMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.HangupMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.OfferMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.ResponseMessage;
import com.unirobot.webrtc.unibocom.signaling.message.InRoomMessage.RoomMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.CallingFailMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.EnterFailMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.ExitFailMessage;
import com.unirobot.webrtc.unibocom.signaling.message.NotifyMessage.FailMessage;

import org.json.JSONException;
import org.json.JSONObject;

/**
 * Created by San Vo on 13/03/2018.
 */

public class MessageFactory {
    public static Message create(JSONObject msg) throws JSONException {
        String type = msg.getString("type");
        switch (type) {
            case "room":
                return new RoomMessage(msg);
            case "call":
                return new CallMessage(msg);
            case "response":
                return new ResponseMessage(msg);
            case "offer":
                return new OfferMessage(msg);
            case "answer":
                return new AnswerMessage(msg);
            case "candidate":
                return new CandidateMessage(msg);
            case "hangup":
                return new HangupMessage(msg);
            case "notify":
                String event = msg.getString("event");
                switch (event) {
                    case "enter_fail":
                        return new EnterFailMessage(msg);
                    case "exit_fail":
                        return new ExitFailMessage(msg);
                    case "call_fail":
                        return new CallingFailMessage(msg);
                    case "message_fail":
                        return new FailMessage(msg);
                    default:
                        Log.w("Unibo - MessageFactory", "Not handle notify message with event " + event);
                        break;
                }
                break;
            default:
                Log.w("Unibo - MessageFactory", "Not handle message type " + type);
                break;
        }
        return null;
    }
}
