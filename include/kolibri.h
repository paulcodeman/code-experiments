#pragma option OST
#pragma option ON
#pragma option cri-
#pragma option -CPA
#initallvar 0
#jumptomain FALSE

#startaddress 0

#code32 TRUE

char   os_name[8]   = {'M','E','N','U','E','T','0','1'};
dword  os_version   = 0x00000001;
dword  start_addr   = #______INIT______;
dword  final_addr   = #______STOP______+32;
dword  alloc_mem    = 0x8000;
dword  x86esp_reg   = 0x8000;
dword  I_Param      = #param;
dword  I_Path       = #program_path;
char param[4096];
char program_path[4096];

#define bool      int

#define NULL      0
#define OLD      -1
#define true      1
#define false     0

//Process Events
#define evReDraw  1
#define evKey     2
#define evButton  3
#define evExit    4
#define evDesktop 5
#define evMouse   6
#define evIPC     7
#define evNetwork 8
#define evDebug   9

//Event mask bits for function 40
#define EVM_REDRAW                1b
#define EVM_KEY                  10b
#define EVM_BUTTON              100b
#define EVM_EXIT               1000b
#define EVM_DESKTOPBG         10000b
#define EVM_MOUSE            100000b
#define EVM_IPC             1000000b
#define EVM_STACK          10000000b
#define EVM_DEBUG         100000000b
#define EVM_STACK2       1000000000b
#define EVM_MOUSE_FILTER  0x80000000
#define EVM_CURSOR_FILTER 0x40000000

//Button options
#define BT_DEL      0x80000000
#define BT_HIDE     0x40000000
#define BT_NOFRAME  0x20000000

#define OFFSET_STRING_DATA  8
#define OFFSET_STRING_LEN   0
#define OFFSET_STRING_POINT 4

#define CLOSE_BTN 1

:dword PY__NAME__ = "__main__";

//-------------------------------------------------------------------------

inline fastcall dword calc(EAX) { RETURN EAX; }

void ______INIT______()
{
	/* Init Allocate */
	EAX = 68;
	EBX = 11;
	$int 0x40
	main();
	/* Exit */
	EAX = -1;
	$int 0x40;
}
______STOP______:
