#!/usr/bin/python

#       http://www.apache.org/licenses/LICENSE-2.0
#
#   Unless required by applicable law or agreed to in writing, software
#   distributed under the License is distributed on an "AS IS" BASIS,
#   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
#   See the License for the specific language governing permissions and
#   limitations under the License.
#
#

from xmlrpclib import ServerProxy, Error
import sys, getopt
import socket

socket.setdefaulttimeout(5)
#channel = sys.argv[1]


def llRemoteData(Channel, Int, String):
  client = ServerProxy(gateway)
  try:
    return client.llRemoteData({"Channel" : Channel, "IntValue" : Int, "StringValue" : String})
  except Error as v:
    # print "Error: %s %s" % (v, channel)
    return False



def usage():

  help_text =  '''

        rpctest usage...
	
        rpctest -c|--channel channel -g|--gateway gateway_uri

'''

  print help_text


if __name__ == "__main__":

  reply = False
  count = 0

  try:
    opts, args = getopt.getopt(sys.argv[1:], "c:g:h", ["gateway=", "channel=", "help"])
  except getopt.GetoptError as err:
    print str(err)
    usage()
    sys.exit(2)

  gateway = None
  channel = None

  for o, a in opts:
    if o in ("-g", "--gateway"):
      gateway = a

    elif o in ("-c", "--channel"):
      channel = a

    elif o in ("-h", "--help"):
      usage()
      sys.exit(2)

    else:
      assert False, "unhandled option"
      usage()
      sys.exit(2)



  #print "\nChannel: %s\nGateway: %s\n"%(channel,gateway)

  if (channel == None or gateway == None):
    usage()
    sys.exit(2)

  while reply == False and count < 4:

    try:
      # print "Trying %s - tried %s times." % (channel, str(count))
      count = count +1
      reply = llRemoteData(channel, 0, "Hello from " + sys.platform);
      #if (reply == False):
        #print "Issue connecting to channel %s" % channel

    except:
      print "Re-trying %s" % channel

  if ( reply == False ):
    print "Could not connect to channel %s" % channel
    sys.exit(2)

  if ('success' in reply):
    print "Error! %s"%reply['errorMessage']
    sys.exit(2) 

  message = None
  hits = None
  if ('StringValue' in reply[0]):
    message = reply[0]['StringValue']
  if ('IntValue' in reply[0]):
    hits = reply[0]['IntValue']

  print '\n%s\nChannel: %s"\n"Object has been contacted %s times\nContact made in %d attempts\n'%(message,channel,hits,count)



