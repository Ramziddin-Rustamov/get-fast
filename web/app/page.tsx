import { redirect } from "next/navigation";

export default function Home() {
  // The dashboard layout guards auth and bounces to /login when needed.
  redirect("/dashboard");
}
