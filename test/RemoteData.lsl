// Copyright (c) 2008-2012 BlueWall Information Technologies, LLC
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.
//

key rpcChan;
integer count;
string message;

default
{
    state_entry()
    {
        llSay(0, "Script running");
        count = 0;
        message = llGetObjectName() + 
            " Says - Hello from " + llGetRegionName();
            
        llSetText("XML-RPC Test",<1,0,0>,1.0);
        llOpenRemoteDataChannel();
    }
    
    touch_start(integer _det)
    {
        message = llGetObjectName() + 
            " Says - Hello from " + llGetRegionName();
        llOpenRemoteDataChannel();
    }
    
    remote_data(integer type, key channel, key message_id, string sender, integer ival, string sval)
    {
        if (type == REMOTE_DATA_CHANNEL)
        {
            rpcChan = channel;
            llSay(0, "Channel: " + (string) channel);
        }
        
        else if (type == REMOTE_DATA_REQUEST)
        {
            llSay(0, sval);
            llSay(0, channel);
            llRemoteDataReply(channel, message_id, message, count++);
        }
    }
}
